<?php
/**
 * **************************************************************
 * Copyright notice
 *
 * (c) 2010 Agentur am Wasser | Maeder & Partner AG
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * **************************************************************
 *
 * @author     Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @copyright  Copyright (c) 2010 Agentur am Wasser | Maeder & Partner AG (http://www.agenturamwasser.ch)
 * @license    http://www.gnu.org/copyleft/gpl.html     GNU General Public License
 * @category   TYPO3
 * @package    pagenotfoundhandling
 * @version    $Id$
 */

/**
 * Main 404 handling class
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 */
class tx_pagenotfoundhandling
{
    /**
     * The params given from tslib_fe pageErrorHandler
     *
     * @see tslib_fe
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
	 * Main method called through tslib_fe::pageErrorHandler()
	 *
	 * @param array $params
	 * @param tslib_fe $tslib_fe
	 * @return string
	 */
    public function main($params, tslib_fe $tslib_fe)
    {
        $this->_get = t3lib_div::_GET();

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

        if(t3lib_extMgm::isLoaded('static_info_tables') && !empty($this->_forceLanguage)) {
            $res = $GLOBALS['TYPO3_DB']->sql_query('
                SELECT
                    *
                FROM
                    sys_language
                LEFT JOIN
                    static_languages
                    ON sys_language.static_lang_isocode=static_languages.uid
                WHERE
                    sys_language.uid='.$this->_forceLanguage.'
                LIMIT 1');
            if(($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
                // workaround for english because it has no lg_typo3 but is default language
                if($row['lg_iso_2'] === 'EN') {
                    $lang = 'default';
                } elseif(!empty($row['lg_typo3'])) {
                    $lang = $row['lg_typo3'];
                }
            }
        }

        require_once PATH_typo3 . 'sysext/lang/lang.php';
        $language = t3lib_div::makeInstance('language');
        $language instanceof language;
        $language->init($lang);
        $language->includeLLFile('EXT:pagenotfoundhandling/locallang_404.xml');

        if(!empty($this->_conf['locallangFile'])) {
            $language->includeLLFile($this->_conf['locallangFile']);
        }

        $html = str_replace('###TITLE###', $language->getLL('page_title', 1), $html);
        $html = str_replace('###MESSAGE###', $language->getLL('page_message', 1), $html);
        $html = str_replace('###REASON_TITLE###', $language->getLL('reason_title', 1), $html);
        $html = str_replace('###REASON###', $this->_params['reasonText'], $html);
        $html = str_replace('###CURRENT_URL_TITLE###', $language->getLL('current_url_title', 1), $html);
        $html = str_replace('###CURRENT_URL###', $this->_params['currentUrl'], $html);
        return $html;
    }

    /**
     * Setup language from URL
     *
     * @return void
     */
    protected function _setupLanguage()
    {
        $language = (int) $this->_get[$this->_languageParam];

        if($language) {
            require_once t3lib_extMgm::extPath('pagenotfoundhandling') . 'class.tx_pagenotfoundhandling_LanguageSelect.php';
            if(array_key_exists($language, tx_pagenotfoundhandling_LanguageSelect::getLanguages(true))) {
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
        $domain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_domain', 'domainName=\'' . $domain . '\' AND hidden=0');
//var_dump($url);
        if($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 1) {
            if($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                if($row['tx_pagenotfoundhandling_enable']) {
                    $this->_default404Page = (int) $row['tx_pagenotfoundhandling_default404Page'];
                    $this->_defaultTemplateFile = 'uploads/tx_pagenotfoundhandling/' . $row['tx_pagenotfoundhandling_defaultTemplateFile'];
                    $this->_ignoreLanguage = (bool) $row['tx_pagenotfoundhandling_ignoreLanguage'];
                    $this->_forceLanguage = (int) $row['tx_pagenotfoundhandling_forceLanguage'];
                    $this->_languageParam = $row['tx_pagenotfoundhandling_languageParam'];

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

            if(isset($conf['default403TemplateFile'])) {
                // reset the 404Page, because the page could come from default404Page and override this templateFile setting
                $this->_default404Page = 0;
                $this->_defaultTemplateFile = (string) $conf['default403TemplateFile'];
            }

            if(isset($conf['default403Page'])) {
                $this->_default404Page = (int) $conf['default403Page'];
            }
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

    		$now = $GLOBALS['SIM_ACCESS_TIME'];
    		$where = 'uid=' . $this->_default404Page . ' AND deleted=0 AND hidden=0 AND (starttime=0 OR starttime =\'\' OR starttime<=' . $now .') AND (endtime=0 OR endtime =\'\' OR endtime>' . $now .')';

			$pageRow = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', $where);
			if(count($pageRow) === 1) {
				$pageRow = current($pageRow);
				$url = t3lib_div::locationHeaderUrl('/');
				$url .= 'index.php?id=' . $this->_default404Page . '&loopPrevention=1';

				if(!empty($this->_forceLanguage)) {
					$url .= '&L=' . $this->_forceLanguage;
				}

				if($this->_isForbiddenError) {
                    if(count($this->_additional403GetParams)) {
                        $url .= '&' . implode('&', $this->_additional403GetParams);
                    }
				} else {
    				if(count($this->_additional404GetParams)) {
    				    $url .= '&' . implode('&', $this->_additional404GetParams);
    				}
				}

                $headers = array(
                    'User-agent: ' . t3lib_div::getIndpEnv('HTTP_USER_AGENT'),
                    'Referer: ' . t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')
                );

				$html = t3lib_div::getURL($url, 0, $headers);

			}
    	}
    	if($html === null && isset($this->_conf['defaultTemplateFile']) && !empty($this->_conf['defaultTemplateFile'])) {
			$file = t3lib_div::getFileAbsFileName($this->_conf['defaultTemplateFile']);

			if(!empty($file) && is_readable($file)) {
				$html = file_get_contents($file);
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
                $this->_forbiddenHeader = t3lib_utility_Http::HTTP_STATUS_400;
                break;
            case 2:
                $this->_forbiddenHeader = t3lib_utility_Http::HTTP_STATUS_401;
                break;
            case 3:
                $this->_forbiddenHeader = t3lib_utility_Http::HTTP_STATUS_402;
                break;
            case 4:
                $this->_forbiddenHeader = t3lib_utility_Http::HTTP_STATUS_403;
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
        $this->_additional404GetParams = array_merge($this->_additional404GetParams, t3lib_div::trimExplode('&', $params, true));
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
        $this->_additional403GetParams = array_merge($this->_additional403GetParams, t3lib_div::trimExplode('&', $params, true));
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling.php']);
}
?>