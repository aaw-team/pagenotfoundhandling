<?php
namespace AawTeam\Pagenotfoundhandling\Controller;

/*
 * Copyright 2014-2017 Agentur am Wasser | Maeder & Partner AG
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use AawTeam\Pagenotfoundhandling\Utility\LanguageUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * 404 handling controller
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 */
class PagenotfoundController
{
    /**
     * The params given from TypoScriptFrontendController pageErrorHandler
     *
     * @see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     * @var array
     */
    protected $_params = array();

    /**
     * Config from constants editor in EM
     *
     * @var array
     */
    protected $_conf = array();

    /**
     * Content of $_GET
     *
     * @var array
     */
    protected $_get = array();

    /**
     * Ignore the language parameter in _GET
     *
     * @var boolean
     */
    protected $_ignoreLanguage = false;

    /**
     * Default language parameter in _GET
     *
     * @var boolean
     */
    protected $_languageParam = 'L';

    /**
     * Language uid to force using
     *
     * @var int
     */
    protected $_forceLanguage = 0;

    /**
     * TYPO3 page to fetch as 404 page
     *
     * @var int
     */
    protected $_default404Page = 0;

    /**
     * Template file to render as 404 page
     *
     * @var string
     */
    protected $_defaultTemplateFile = '';

    /**
     * Disable the per-domain configuration
     *
     * @var boolean
     */
    protected $_disableDomainConfig = false;

    /**
     * Default language key
     *
     * @var string
     */
    protected $_defaultLanguageKey = 'default';

    /**
     * Wether the page not found error is because of 'no access' or not
     *
     * @var boolean
     */
    protected $_isForbiddenError = false;

    /**
     * HTTP header to be sent for request on restricted pages
     *
     * @var string
     */
    protected $_forbiddenHeader = '';

    /**
     * Additional _GET params
     *
     * These will be appended to the URL when fetching default404Page
     *
     * @var array
     */
    protected $_additional404GetParams = array();

    /**
     * Additional 403 _GET params
     *
     * These will be appended to the URL when fetching default404Page and
     * $_forbiddenError is true
     *
     * @var array
     */
    protected $_additional403GetParams = array();

    /**
     * Passthrough for the HTTP header 'Content-Type'
     *
     * @var boolean
     */
    protected $_passthroughContentTypeHeader = false;

    /**
     * Send a 'X-Forwarded-For' HTTP header
     *
     * @var boolean
     */
    protected $_sendXForwardedForHeader = false;

    /**
     * Addtional HTTP headers to be sent with the 404/403 page
     *
     * @var array
     */
    protected $_additionalHeaders = array();

    /**
     * Absolute reference prefix
     *
     * Prefixes the URL which fetches the 404 page
     *
     * @var string
     */
    protected $_absoluteReferencePrefix = '';

    /**
     * HTTP digest authentication
     *
     * Format: 'username:password'
     *
     * @var string
     */
    protected $_digestAuthentication = '';

    /**
     * Presesrve frontend user login session when fetching the 404/403 page
     *
     * @var boolean
     */
    protected $_preserveFeuserLogin = false;

    /**
     * Print debugging information when _getUrl() did not receive a valid response
     *
     * @var boolean
     */
    protected $_debugGetUrlError = false;

    /**
     * Request timeout
     *
     * @var integer
     */
    protected $_requestTimeout = 10;

    /**
     * Main method called through TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::pageErrorHandler()
     *
     * @param array $params
     * @param object $controller
     * @return string
     */
    public function main($params, $controller)
    {
        $this->_get = GeneralUtility::_GET();

        // prevent infinite loops
        if($this->_get['loopPrevention']) {
            die('Caught infinite loop');
        }

        $this->_params = $params;

        // check for access errors
        if(isset($this->_params['pageAccessFailureReasons']['fe_group'])
            && $this->_params['pageAccessFailureReasons']['fe_group'] !== array('' => 0)) {
                $this->_isForbiddenError = true;
        }

        $this->_loadConstantsConfig();

        if($this->_disableDomainConfig !== true) {
            $this->_loadDomainConfig();
        }

        // send special HTTP header
        if($this->_isForbiddenError && !empty($this->_forbiddenHeader)) {
            header($this->_forbiddenHeader);
        }

        if(!$this->_ignoreLanguage && empty($this->_forceLanguage)) {
            $this->_setupLanguage();
        }

        $return = $this->_getHtml();

        return $return;
    }

    /**
     * Replace the available markers with localized content
     *
     * Available markers:
     * - ###TITLE###
     * - ###MESSAGE###
     *
     * @param string $html
     * @return string
     */
    protected function _processMarkers($html)
    {
        $lang = $this->_defaultLanguageKey;

        if(ExtensionManagementUtility::isLoaded('static_info_tables') && !empty($this->_forceLanguage)) {

            $qb = $this->_getConnectionForTable('sys_language')->createQueryBuilder();
            $qb->select('*')
                ->from('sys_language')
                ->leftJoin(
                    'sys_language',
                    'static_languages',
                    'static_languages',
                    $qb->expr()->eq('sys_language.static_lang_isocode', $qb->quoteIdentifier('static_languages.uid'))
                )
                ->where(
                    $qb->expr()->eq('sys_language.uid', $qb->createNamedParameter($this->_forceLanguage, \PDO::PARAM_INT))
                )
                ->setMaxResults(1);

            if(($row = $qb->execute()->fetch())) {
                // workaround for english because it has no lg_typo3 but is default language
                if($row['lg_iso_2'] === 'EN') {
                    $lang = 'default';
                } elseif(!empty($row['lg_typo3'])) {
                    $lang = $row['lg_typo3'];
                }
            }
        }

        $language = GeneralUtility::makeInstance('TYPO3\\CMS\\Lang\\LanguageService');
        //$language instanceof \TYPO3\CMS\Lang\LanguageService;
        $language->init($lang);
        $language->includeLLFile('EXT:pagenotfoundhandling/Resources/Private/Language/locallang_404.xml');

        if(!empty($this->_conf['locallangFile'])) {
            $language->includeLLFile($this->_conf['locallangFile']);
        }

        $html = str_replace('###TITLE###', $language->getLL('page_title', 1), $html);
        $html = str_replace('###MESSAGE###', $language->getLL('page_message', 1), $html);
        $html = str_replace('###REASON_TITLE###', $language->getLL('reason_title', 1), $html);
        $html = str_replace('###REASON###', htmlspecialchars($this->_params['reasonText']), $html);
        $html = str_replace('###CURRENT_URL_TITLE###', $language->getLL('current_url_title', 1), $html);
        $html = str_replace('###CURRENT_URL###', htmlspecialchars($this->_params['currentUrl']), $html);
        return $html;
    }

    /**
     * Setup language from URL
     *
     * @return void
     */
    protected function _setupLanguage()
    {
        if (version_compare(TYPO3_version, '9.2', '>=')) {
            /** @var \TYPO3\CMS\Core\Http\ServerRequest $request */
            $request = $GLOBALS['TYPO3_REQUEST'];
            /** @var \TYPO3\CMS\Core\Site\Entity\SiteLanguage $language */
            $language = $request->getAttribute('language', 0);
            if ($language) {
                $language = $language->getLanguageId();
            }
        } else {
            $language = (int) $this->_get[$this->_languageParam];
        }

        if($language) {
            if(array_key_exists($language, LanguageUtility::getLanguages(true))) {
                $this->_forceLanguage = $language;
            }
        }
    }

    /**
     * Store config from a possible domain record
     *
     * @return void
     */
    protected function _loadDomainConfig()
    {
        $domainRecords = $this->_getConnectionForTable('sys_domain')->select(
            ['*'],
            'sys_domain',
            [
                'domainName' => GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')
            ]
        )->fetchAll();

        if(count($domainRecords) == 1) {
            $row = $domainRecords[0];
            if($row['tx_pagenotfoundhandling_enable']) {
                $this->_default404Page = (int) $row['tx_pagenotfoundhandling_default404Page'];
                if ($row['tx_pagenotfoundhandling_defaultTemplateFile']) {
                    $this->_defaultTemplateFile = 'uploads/tx_pagenotfoundhandling/' . $row['tx_pagenotfoundhandling_defaultTemplateFile'];
                }
                $this->_ignoreLanguage = (bool) $row['tx_pagenotfoundhandling_ignoreLanguage'];
                $this->_forceLanguage = (int) $row['tx_pagenotfoundhandling_forceLanguage'];
                $this->_languageParam = $row['tx_pagenotfoundhandling_languageParam'];
                $this->_passthroughContentTypeHeader = (bool) $row['tx_pagenotfoundhandling_passthroughContentTypeHeader'];
                $this->_sendXForwardedForHeader = (bool) $row['tx_pagenotfoundhandling_sendXForwardedForHeader'];
                $this->_additionalHeaders = GeneralUtility::trimExplode('|', $row['tx_pagenotfoundhandling_additionalHeaders'], true);
                $this->_digestAuthentication = trim($row['tx_pagenotfoundhandling_digestAuthentication']);

                // override 404 page with its 403 equivalent (if needed and configured so)
                if($this->_isForbiddenError) {
                    $this->_setForbiddenHeader($row['tx_pagenotfoundhandling_default403Header'], false);

                    if($row['tx_pagenotfoundhandling_default403Page']) {
                        $this->_default404Page = (int) $row['tx_pagenotfoundhandling_default403Page'];
                    }
                }
            }
        }
    }

    /**
     * Store the values from the constants editor
     *
     * @return void
     */
    protected function _loadConstantsConfig()
    {
        $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pagenotfoundhandling']);

        // store all configuration
        $this->_conf = $conf;

        if(isset($conf['default404Page'])) {
            $this->_default404Page = (int) $conf['default404Page'];
        }

        if(isset($conf['defaultTemplateFile'])) {
            $this->_defaultTemplateFile = (string) $conf['defaultTemplateFile'];
        }

        if(isset($conf['additional404GetParams'])) {
            $this->_addAdditionalGetParams($conf['additional404GetParams']);
        }

        if(isset($conf['additional403GetParams'])) {
            $this->_addAdditional403GetParams($conf['additional403GetParams']);
        }

        if(isset($conf['ignoreLanguage'])) {
            $this->_ignoreLanguage = (bool) $conf['ignoreLanguage'];
        }

        if(isset($conf['forceLanguage']) && !empty($conf['forceLanguage'])) {
            $this->_forceLanguage = (int) $conf['forceLanguage'];
        }

        if(isset($conf['disableDomainConfig'])) {
            $this->_disableDomainConfig = (bool) $conf['disableDomainConfig'];
        }

        if(isset($conf['defaultLanguageKey'])) {
            $this->_defaultLanguageKey = (string) $conf['defaultLanguageKey'];
        }

        if(isset($conf['languageParam'])) {
            $this->_languageParam = $conf['languageParam'];
        }

        // override 404 page/template with the 403 equivalents (if needed and configured so)
        if($this->_isForbiddenError) {
            $this->_setForbiddenHeader($conf['default403Header']);

            if(isset($conf['default403TemplateFile']) && !empty($conf['default403TemplateFile'])) {
                // reset the 404Page, because the page could come from default404Page and override this templateFile setting
                $this->_default404Page = 0;
                $this->_defaultTemplateFile = (string) $conf['default403TemplateFile'];
            }

            if(isset($conf['default403Page']) && !empty($conf['default403Page'])) {
                $this->_default404Page = (int) $conf['default403Page'];
            }
        }

        if(isset($conf['passthroughContentTypeHeader'])) {
            $this->_passthroughContentTypeHeader = (bool) $conf['passthroughContentTypeHeader'];
        }

        if(isset($conf['sendXForwardedForHeader'])) {
            $this->_sendXForwardedForHeader = (bool) $conf['sendXForwardedForHeader'];
        }

        if(isset($conf['additionalHeaders'])) {
            $this->_additionalHeaders = GeneralUtility::trimExplode('|', $conf['additionalHeaders'], true);
        }

        if(isset($conf['digestAuthentication'])) {
            $this->_digestAuthentication = trim($conf['digestAuthentication']);
        }

        if(isset($conf['absoluteReferencePrefix'])) {
            // remove '/' and whitespaces
            $absoluteReferencePrefix = \trim(\trim($conf['absoluteReferencePrefix'], '/'));

            // check for double dots (..) in the path
            if (\preg_match('/([\.]|\%2e){2}/i', $absoluteReferencePrefix)) {
                throw new \InvalidArgumentException('EXT:pagenotfoundhandling: absoluteReferencePrefix must not contain double dots', 1403536458);
            }

            $this->_absoluteReferencePrefix = $absoluteReferencePrefix;
        }

        if(isset($conf['preserveFeuserLogin'])) {
            $this->_preserveFeuserLogin = (bool) $conf['preserveFeuserLogin'];
        }

        if(isset($conf['debugGetUrlError'])) {
            $this->_debugGetUrlError = (bool) $conf['debugGetUrlError'];
        }

        if(isset($conf['requestTimeout']) && MathUtility::canBeInterpretedAsInteger($conf['requestTimeout']) && $conf['requestTimeout'] >= 0) {
            $this->_requestTimeout = (int) $conf['requestTimeout'];
        }
    }

    /**
     * Returns the content of the 404 page
     *
     * @return string
     */
    protected function _getHtml()
    {
        $html = null;
        if(isset($this->_default404Page) && !empty($this->_default404Page)) {

            $pageRow = $this->_getConnectionForTable('pages')->select(
                ['*'],
                'pages',
                [
                    'uid' => $this->_default404Page,
                ]
            )->fetchAll();

            if(count($pageRow) === 1) {
                $pageRow = current($pageRow);
                $url = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/';

                if ($this->_absoluteReferencePrefix) {
                    $url .= $this->_absoluteReferencePrefix . '/';
                }

                $url .= 'index.php?id=' . $this->_default404Page . '&loopPrevention=1';

                // append language parameter to query string
                $url .= $this->_getLanguageQueryString();

                if($this->_isForbiddenError) {
                    if(count($this->_additional403GetParams)) {
                        $url .= '&' . implode('&', $this->_additional403GetParams);
                    }
                } else {
                    if(count($this->_additional404GetParams)) {
                        $url .= '&' . implode('&', $this->_additional404GetParams);
                    }
                }

                $url = str_replace('###CURRENT_URL###', urlencode($this->_params['currentUrl']), $url);

                $headers = array(
                    'User-agent: ' . GeneralUtility::getIndpEnv('HTTP_USER_AGENT'),
                    'Referer: ' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')
                );

                // Preserve a frontend user login session
                if ($this->_preserveFeuserLogin) {
                    $frontendUserAuthentication = $this->_getTyposcriptFrontendController()->fe_user;
                    if (is_array($frontendUserAuthentication->user) && $frontendUserAuthentication->user['uid'] > 0) {
                        $headers[] = 'Cookie: ' . rawurlencode($frontendUserAuthentication->getCookieName()) . '=' . rawurlencode($frontendUserAuthentication->id);
                        $this->_sendXForwardedForHeader = true;
                    }
                }

                if ($this->_sendXForwardedForHeader) {
                    $headers[] = 'X-Forwarded-For: ' . GeneralUtility::getIndpEnv('REMOTE_ADDR');
                }

                $url = $this->_processErrorPageUrl($url);
                $report = array();
                $html = $this->_getUrl($url, (int) $this->_passthroughContentTypeHeader, $headers, $report);
                if ($this->_passthroughContentTypeHeader && $html !== null) {
                    // split response header and body
                    list ($responseHeaders, $html) = GeneralUtility::trimExplode(CRLF . CRLF, $html, false, 2);

                    // content-type passthrough
                    if (array_key_exists('content_type', $report) && strlen($report['content_type'])) {
                        header('Content-Type: ' . $report['content_type']);
                    }
                }
            }
        }
        if($html === null && !empty($this->_defaultTemplateFile)) {
            $file = GeneralUtility::getFileAbsFileName($this->_defaultTemplateFile);
            if(!empty($file) && is_readable($file)) {
                $html = file_get_contents($file);
            }
        }

        // send additional HTTP headers
        if (count($this->_additionalHeaders)) {
            // disallow sending 'Location' header (redirecting)
            foreach ($this->_additionalHeaders as $header) {
                if (!preg_match('~^(HTTP\\/|Location:)~i', $header)) {
                    header($header);
                }
            }
        }

        if(!is_null($html)) {
            return $this->_processMarkers($html);
        }

        return $this->_processMarkers('<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <title>###TITLE###</title>
    </head>
    <body>
        <div id="page">
            <h1>###TITLE###</h1>
            <p>###MESSAGE###</p>
        </div>
    </body>
</html>');
    }

    /**
     * Wrapper method for GeneralUtility::getURL();
     *
     * Additionally, this method adds a HTTP 'Authorization' header, when one is
     * present in the current request.
     *
     * @throws \Exception
     * @param string $url
     * @param int $includeHeaders
     * @param array $headers
     * @param array $report
     * @return mixed
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::getURL()
     */
    protected function _getUrl($url, $includeHeaders = 0, array $headers = array(), &$report = null)
    {
        // handle http authorization
        $digestAuthorization = false;
        $basicAuthorization = '';
        $requestHeaders = $this->_getAllHttpHeaders();
        if (array_key_exists('Authorization', $requestHeaders)) {
            $authorizationHeader = $requestHeaders['Authorization'];
            // Authorization 'basic' support
            if (strpos($authorizationHeader, 'Basic ') === 0) {
                // check the header value for authentication basic,
                // only base64 characters are allowed
                $basicAuthorization = substr($authorizationHeader, 6);
                if (preg_match('~[^a-zA-Z0-9+/=]~', $basicAuthorization) !== 0) {
                    $basicAuthorization = '';
                }
            } elseif (strpos($authorizationHeader, 'Digest ') === 0) {
                $digestAuthorization = true;
            }
        }

        // Use RequestFactory in TYPO3 >= 8.1
        if (version_compare(TYPO3_version, '8.1', '>=')) {
            // Setup options for the request
            $options = [
                \GuzzleHttp\RequestOptions::HEADERS => [],
                \GuzzleHttp\RequestOptions::TIMEOUT => $this->_requestTimeout,
                \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => $this->_requestTimeout,
            ];
            foreach ($headers as $header) {
                list($headerName, $headerValue) = explode(':', $header, 2);
                $options[\GuzzleHttp\RequestOptions::HEADERS][$headerName] = ltrim($headerValue);
            }

            // Handle authorization
            if (!empty($basicAuthorization)) {
                list ($username, $password) = explode(':', base64_decode($basicAuthorization), 2);
                $options[\GuzzleHttp\RequestOptions::AUTH] = [$username, $password];
            } elseif ($digestAuthorization) {
                list ($username, $password) = GeneralUtility::trimExplode(':', $this->_digestAuthentication, false, 2);
                $options[\GuzzleHttp\RequestOptions::AUTH] = [$username, $password, 'digest'];
            }

            if (isset($report)) {
                $report['lib'] = 'GuzzleHttp';
            }

            /** @var \TYPO3\CMS\Core\Http\RequestFactory $requestFactory */
            $requestFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Http\RequestFactory::class);
            try {
                $response = $requestFactory->request($url, 'GET', $options);
                $return = '';

                // Do it almost the same way as in GeneralUtility::getUrl()
                $includeHeaders = (int)$includeHeaders;
                if ($includeHeaders) {
                    foreach ($response->getHeaders() as $name => $values) {
                        $return .= $name . ": " . implode(", ", $values) . CRLF;
                    }
                    $return .= CRLF;
                }
                if ($includeHeaders !== 2) {
                    $return .= $response->getBody()->getContents();
                }
                if (isset($report)) {
                    if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
                        $report['http_code'] = $response->getStatusCode();
                        $report['content_type'] = $response->getHeader('Content-Type');
                        $report['error'] = $response->getStatusCode();
                        $report['message'] = $response->getReasonPhrase();
                    } elseif (!empty($return)) {
                        $report['error'] = $response->getStatusCode();
                        $report['message'] = $response->getReasonPhrase();
                    } elseif ($includeHeaders) {
                        // Set only for $includeHeader to work exactly like PHP variant
                        $report['http_code'] = $response->getStatusCode();
                        $report['content_type'] = $response->getHeader('Content-Type');
                    }
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $return = false;
                if (isset($report)) {
                    $report['error'] = $e->getCode();
                    $report['message'] = $e->getMessage();
                    $report['exception'] = $e;
                }
            }
        } elseif ($digestAuthorization) {
            // Digest authorization in TYPO3 < 8.1
            $return = $this->_getUrlWithDigestAuthentication($url, $includeHeaders, $headers, $report);
        } else {
            // Default for TYPO3 < 8.1
            if (!empty($basicAuthorization)) {
                $headers[] = 'Authorization: Basic ' . $basicAuthorization;
            }
            $return = GeneralUtility::getURL($url, $includeHeaders, $headers, $report);
        }

        if ($return === false) {
            $return = null;
            if ($this->_debugGetUrlError) {
                \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump([
                    '$url' => $url,
                    '$includeHeaders' => $includeHeaders,
                    '$headers' => $headers,
                    '$report' => $report,
                    '$digestAuthorization' => $digestAuthorization,
                    '$basicAuthorization' => $basicAuthorization,
                ], 'pagenotfoundhandling debug: _getUrl()');

                if (isset($report['exception']) && $report['exception'] instanceof \GuzzleHttp\Exception\RequestException) {
                    $return = '<h1>pagenotfoundhandling debug</h1>';
                    if ($report['exception']->hasResponse()) {
                        $return .= '<h2>Response content:</h2>' . $report['exception']->getResponse()->getBody()->getContents();
                    } else {
                        $return .= '<p>The response does not have any content.';
                        if ($this->_requestTimeout > 0) {
                            $return .= ' Maybe the request timed out (after ' . $this->_requestTimeout . 's)?';
                        }
                        $return .= '</p>';
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Wrapper method for the cURL-enabled part of GeneralUtility::getURL()
     *
     * Additionally, this method configures cURL to use HTTP digest
     * authentication.
     *
     * Note: other than GeneralUtility::getURL(), this method supports no
     * redirection itself. Redirection won't work, when cURL option
     * CURLOPT_FOLLOWLOCATION con't be applied!
     *
     * @throws \Exception
     * @param string $url
     * @param int $includeHeaders
     * @param array $headers
     * @param array $report
     * @return mixed
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::getURL()
     */
    protected function _getUrlWithDigestAuthentication($url, $includeHeaders = 0, array $headers = array(), &$report = null)
    {
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] != '1') {
            throw new \Exception('cURL usage must be enabled ($GLOBALS[\'TYPO3_CONF_VARS\'][\'SYS\'][\'curlUse\']) when using HTTP digest authentication.');
        }

        // prepare username/password
        list ($username, $password) = GeneralUtility::trimExplode(':', $this->_digestAuthentication, false, 2);

        // do (almost) the same things as in GeneralUtility::getURL();
        if (!function_exists('curl_init') || !($ch = curl_init())) {
            if (isset($report)) {
                $report['error'] = -1;
                $report['message'] = 'Couldn\'t initialize cURL.';
            }
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, (intval($includeHeaders) > 0));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, max(0, (int) $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlTimeout']));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $username. ':' . $password);
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']) {
            curl_setopt($ch, CURLOPT_PROXY, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']);
            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyNTLM']) {
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
            }
            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']) {
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']);
            }
            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']);
            }
        }

        // apply the http headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // execute the request
        $content = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);

        // strip http headers
        // @see GeneralUtility::stripHttpHeaders()
        if ($includeHeaders < 1) {
            $headersEndPos = strpos($content, CRLF . CRLF);
            if ($headersEndPos !== false) {
                $content = substr($content, $headersEndPos + 4);
            }
        }

        if (isset($report)) {
            if ($content === false) {
                $report['error'] = curl_errno($ch);
                $report['message'] = curl_error($ch);
            } elseif ($includeHeader) {
                $report['http_code'] = $curlInfo['http_code'];
                $report['content_type'] = $curlInfo['content_type'];
            }
        }
        curl_close($ch);
        return $content;
    }

    /**
     * Returns all HTTP headers from the current request
     *
     * @return array
     */
    protected function _getAllHttpHeaders()
    {
        if (function_exists('getallheaders')) {
            return \getallheaders();
        } else {
            $headers = array();
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $name = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$name] = $value;
                }
            }
            return $headers;
        }
    }

    /**
     * Sets the forbiddenHeader to the accurate value
     *
     * @param int $number
     * @return void
     */
    protected function _setForbiddenHeader($number, $overrideIfEmpty = true)
    {
        switch((int) $number) {
            case -1:
                $this->_forbiddenHeader = '';
                break;
            case 1:
                $this->_forbiddenHeader = HttpUtility::HTTP_STATUS_400;
                break;
            case 2:
                $this->_forbiddenHeader = HttpUtility::HTTP_STATUS_401;
                break;
            case 3:
                $this->_forbiddenHeader = HttpUtility::HTTP_STATUS_402;
                break;
            case 4:
                $this->_forbiddenHeader = HttpUtility::HTTP_STATUS_403;
                break;
            default :
                if($overrideIfEmpty) {
                    $this->_forbiddenHeader = '';
                }
                break;
        }
    }

    /**
     * Add additional _GET params
     *
     * @param string $params  (works like additionalParams in typolink)
     * @return void
     */
    protected function _addAdditionalGetParams($params)
    {
        $params = $this->_normalizeGetParams($params);
        $this->_additional404GetParams = array_merge($this->_additional404GetParams, GeneralUtility::trimExplode('&', $params, true));
    }

    /**
     * Add additional 403 _GET params
     *
     * @param string $params  (works like additionalParams in typolink)
     * @return void
     */
    protected function _addAdditional403GetParams($params)
    {
        $params = $this->_normalizeGetParams($params);
        $this->_additional403GetParams = array_merge($this->_additional403GetParams, GeneralUtility::trimExplode('&', $params, true));
    }

    /**
     * Normalize the _GET params for using in the page_fetching of _getHtml()
     *
     * @param string $params
     * @return void
     */
    protected function _normalizeGetParams($params)
    {
        // strip out params that will be generated in _getHtml()
        return preg_replace('/&?(id|loopPrevention|' . $this->_languageParam . ')=[^&]*/', '', $params);
    }

    /**
     * Returns a string to append to an URL (ex: "&L=1"). The string consists of
     * the languageParam (mostly "L") and the needed sys_language_uid. When no
     * language is requested, an empty string is retured.
     *
     * @return string
     */
    protected function _getLanguageQueryString()
    {
        if(!empty($this->_forceLanguage)) {
            return '&' . rawurlencode($this->_languageParam) . '=' . rawurlencode($this->_forceLanguage);
        }
        return '';
    }

    /**
     * @param string $url
     * @return string
     */
    protected function _processErrorPageUrl($url)
    {
        if (version_compare(TYPO3_version, '9.2', '>=')) {
            /** @var \TYPO3\CMS\Core\Http\ServerRequest $request */
            $request = $GLOBALS['TYPO3_REQUEST'];
            /** @var \TYPO3\CMS\Core\Site\Entity\Site $site */
            $site = $request->getAttribute('site', null);

            // Note: at the moment, we only support the TYPO3 built-in Site object
            if ($site instanceof \TYPO3\CMS\Core\Site\Entity\Site) {
                // Analyze $url
                $parsedUrl = parse_url($url);
                $queryParams = [];
                parse_str($parsedUrl['query'], $queryParams);

                // Get the page uid (and remove from $queryParams)
                $errorPageUid = (int) $queryParams['id'];
                unset($queryParams['id']);

                // Get the language (and remove from $queryParams)
                if (array_key_exists($this->_languageParam, $queryParams)) {
                    $language = $site->getLanguageById($queryParams[$this->_languageParam]);
                    unset($queryParams[$this->_languageParam]);
                    $queryParams['_language'] = $language;
                }

                // Finally create the new $url
                $url = (string)$site->getRouter()->generateUri(
                    $errorPageUid,
                    $queryParams
                );
            }
        }
        return $url;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function _getTyposcriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    protected function _getConnectionForTable(string $tableName = null)
    {
        if ($tableName !== null) {
            return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
        }
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
    }
}
