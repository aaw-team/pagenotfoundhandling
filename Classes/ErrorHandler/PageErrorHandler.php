<?php
declare(strict_types=1);
namespace AawTeam\Pagenotfoundhandling\ErrorHandler;

/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
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

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageErrorHandler
 */
class PageErrorHandler implements PageErrorHandlerInterface
{
    protected const HTTP_HEADER_XGENERATEDBY = 'EXT:pagenotfoundhandling';
    protected const HTTP_HEADER_XERRORREASON_INFINITELOOP = 'Infinite loop detected';

    /**
     * @var int
     */
    protected $statusCode = 0;

    /**
     * @var array
     */
    protected $errorHandlerConfiguration = [];

    /**
     * @var array
     */
    protected $siteConfiguration = null;

    /**
     * @param int $statusCode
     * @param array $errorHandlerConfiguration
     */
    public function __construct(int $statusCode, array $errorHandlerConfiguration, array $siteConfiguration = null)
    {
        $this->statusCode = $statusCode;
        $this->errorHandlerConfiguration = $errorHandlerConfiguration;
        $this->siteConfiguration = $siteConfiguration;
    }

    /**
     * {@inheritDoc}
     * @see \TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface::handlePageError()
     */
    public function handlePageError(ServerRequestInterface $request, string $message, array $reasons = []): ResponseInterface
    {
        // Infinite loop detection
        if ($request->getQueryParams()['loopPrevention']) {
            $this->getLogger()->error('Detected infinite loop', [
                'requestURI' => (string)$request->getUri(),
                'referer' => $request->getServerParams()['HTTP_REFERER'],
            ]);
            return $this->getInfiniteLoopDetectedResponse();
        }

        // Merge current site configuration
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        $this->mergeSiteConfiguration($site);

        $this->getLogger()->debug('Startup', [
            'site' => $site !== null ? $site->getIdentifier() : '',
            'requestURI' => (string)$request->getUri(),
            'message' => $message,
            'reasons' => $reasons,
            'statusCode' => $this->statusCode,
            'errorHandlerConfiguration' => $this->errorHandlerConfiguration,
        ]);

        // Generate the errorPage URI
        $errorPageURI = $this->generateErrorPageUri($request);
        $this->getLogger()->notice('Fetching error page', [
            'currentURI' => (string)$request->getUri(),
            'errorPageURI' => (string)$errorPageURI,
        ]);

        // Generate request options (@see http://docs.guzzlephp.org/en/stable/request-options.html)
        $errorPageRequestOptions = $this->generateErrorPageRequestOptions($request, $errorPageURI);
        $this->getLogger()->debug('Generate error page request options', [
            'errorPageRequestOptions' => $errorPageRequestOptions,
        ]);

        // Fetch the error page
        /** @var \TYPO3\CMS\Core\Http\RequestFactory $requestFactory */
        $requestFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Http\RequestFactory::class);
        $detectedInfiniteLoop = false;
        try {
            $errorPageResponse = $requestFactory->request((string)$errorPageURI, 'GET', $errorPageRequestOptions);
            $errorPageContents = $errorPageResponse->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse() && $this->isInfiniteLoopDetectedResponse($e->getResponse())) {
                $detectedInfiniteLoop = true;
                // Note: this is logged at the 'incoming' side, see above
                return $this->getInfiniteLoopDetectedResponse();
            }
        } catch (\Exception $e) {
        } finally {
            if ($e) {
                if ($this->siteConfiguration['debugErrorPageRequestException']) {
                    // Return a response with debug content
                    return $this->getDebugErrorPageRequestExceptionResponse($errorPageURI, $errorPageRequestOptions, $e);
                }
                if ($detectedInfiniteLoop !== true) {
                    // Log the exception
                    $this->getLogger()->error('Failed to fetch error page', [
                        'errorPageURI' => (string)$errorPageURI,
                        'exception' => [
                            'type' => get_class($e),
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                        ],
                    ]);
                    throw $e;
                }
            }
        }

        // Replace old-style markers
        $errorPageContents = str_replace('###REASON###', htmlspecialchars($message), $errorPageContents);
        $errorPageContents = str_replace('###CURRENT_URL###', htmlspecialchars((string)$request->getUri()), $errorPageContents);

        // Create the response
        $response = $this->createResponse($errorPageContents, $this->statusCode);

        // Passthrough the 'Content-Type' header
        if ($this->siteConfiguration['passthroughContentTypeHeader'] && $errorPageResponse->hasHeader('content-type')) {
            $response = $response->withHeader('Content-Type', $errorPageResponse->getHeaderLine('content-type'));
        }

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @throws \InvalidArgumentException
     * @return UriInterface
     */
    protected function generateErrorPageUri(ServerRequestInterface $request): UriInterface
    {
        // Analyze error page
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $urlParams = $linkService->resolve($this->errorHandlerConfiguration['errorPage']);
        if ($urlParams['type'] !== 'page') {
            throw new \InvalidArgumentException('errorPage must be a TYPO3 page URL t3://page..');
        }

        // Build additional GET params
        $queryString = '';
        if ($this->siteConfiguration['additionalGetParams']) {
            $queryString .= '&' . trim($this->siteConfiguration['additionalGetParams'], '&');
        }
        if ($this->errorHandlerConfiguration['additionalGetParams']) {
            $queryString .= '&' . trim($this->errorHandlerConfiguration['additionalGetParams'], '&');
        }
        // Setup query parameters
        $requestUriParameters = [];
        parse_str($queryString, $requestUriParameters);
        // Remove reserved names from query string
        $requestUriParameters = array_filter($requestUriParameters, function($key) {
            return !in_array(strtolower($key), ['id', 'chash', 'l', 'mp']);
        }, ARRAY_FILTER_USE_KEY);

        // Compose the request URI
        if (version_compare(TYPO3_version, '9', '>=')) {
            /** @var \TYPO3\CMS\Core\Site\Entity\Site $site */
            $site = $request->getAttribute('site', null);

            // Determine language to request:
            // 1. Force a language
            // 2. Use currently requested language
            // 3. Fallback to default language
            $language = null;
            if ($this->siteConfiguration['forceLanguage'] > -1) {
                try {
                    $language = $site->getLanguageById($this->siteConfiguration['forceLanguage']);
                } catch (\InvalidArgumentException $e) {
                    if ($e->getCode() !== 1522960188) {
                        throw $e;
                    }
                }
            } elseif ($this->siteConfiguration['forceLanguage'] == -1) {
                $language = $request->getAttribute('language', null);
            }
            // Fallback to default if language could not be found
            if (!$language) {
                $language = $site->getDefaultLanguage();
            }

            // Add the required GET params
            $defaultRequestUriParameters = [
                '_language' => $language,
                'loopPrevention' => 1,
            ];
            ArrayUtility::mergeRecursiveWithOverrule($requestUriParameters, $defaultRequestUriParameters);

            // Create the PSR URI object
            $requestUri = $site->getRouter()->generateUri(
                (int)$urlParams['pageuid'],
                $requestUriParameters
            );
        } else {
            // Do it 'pre-v9-style'
            $url = rtrim(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), '/');

            // Legacy: absRefPrexix
            if ($this->siteConfiguration['absoluteReferencePrefix']) {
                $url .= $this->siteConfiguration['absoluteReferencePrefix'];
            } else {
                $url .= '/';
            }
            $url .= 'index.php';

            $requestUriParameters['id'] = (int)$urlParams['pageuid'];
            $requestUriParameters['loopPrevention'] = 1;
            if ($this->siteConfiguration['forceLanguage'] > -1) {
                $requestUriParameters['L'] = (int)$this->siteConfiguration['forceLanguage'];
            } elseif ($this->siteConfiguration['forceLanguage'] == -1 && $request->getQueryParams()['L']) {
                $requestUriParameters['L'] = (int)$request->getQueryParams()['L'];
            }

            // Render query string and append cHash
            $requestUriParameters = GeneralUtility::implodeArrayForUrl('', $requestUriParameters);
            $cHash = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\CacheHashCalculator::class)->generateForParameters($requestUriParameters);
            $requestUriParameters .= $cHash ? '&cHash=' . $cHash : '';

            // Create the PSR URI object
            $requestUri = GeneralUtility::makeInstance(Uri::class, $url . '?' . ltrim($requestUriParameters, '&'));
        }
        return $requestUri;
    }

    /**
     * @param ServerRequestInterface $request
     * @param UriInterface $errorPageURI
     * @return array
     */
    protected function generateErrorPageRequestOptions(ServerRequestInterface $request, UriInterface $errorPageURI): array
    {
        // Compose request options
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'User-Agent' => $request->getServerParams()['HTTP_USER_AGENT'] ?? 'TYPO3 EXT:pagenotfoundhandling',
                'Referer' => $request->getUri()->__toString(),
            ],
        ];
        // Override default timeout
        if ($this->siteConfiguration['requestTimeout'] > 0) {
            $options[\GuzzleHttp\RequestOptions::TIMEOUT] = $this->siteConfiguration['requestTimeout'];
        } elseif ($GLOBALS['TYPO3_CONF_VARS']['HTTP'][\GuzzleHttp\RequestOptions::TIMEOUT] < 1) {
            // Force a 30 sec timeout, when none is set at all
            $options[\GuzzleHttp\RequestOptions::TIMEOUT] = 30;
        }
        // Override default connect_timeout
        if ($this->siteConfiguration['connectTimeout'] > 0) {
            $options[\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT] = $this->siteConfiguration['connectTimeout'];
        } elseif ($GLOBALS['TYPO3_CONF_VARS']['HTTP'][\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT] < 1) {
            // Force a 10 sec connect_timeout, when none is set at all
            $options[\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT] = 10;
        }

        // X-Forwarded-For (append the IP in REMOTE_ADDR)
        $remoteAddress = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        if (filter_var($remoteAddress, FILTER_VALIDATE_IP)) {
            $forwardedForHeader = $request->hasHeader('x-forwarded-for') ? trim($request->getHeaderLine('x-forwarded-for')) : '';
            if ($forwardedForHeader) {
                $forwardedForHeader .= ', ' . $remoteAddress;
            } else {
                $forwardedForHeader = $remoteAddress;
            }
            $options[\GuzzleHttp\RequestOptions::HEADERS]['X-Forwarded-For'] = trim($forwardedForHeader);
        }

        // Request trust
        $currentRequestIsTrusted = GeneralUtility::getIndpEnv('TYPO3_SSL') || $this->siteConfiguration['trustInsecureIncomingConnections'];
        $sendAuthInfoToErrorPage = $errorPageURI->getScheme() === 'https' || $this->siteConfiguration['passAuthinfoToInsecureConnections'];;

        // Passthrough authentication data
        if ($currentRequestIsTrusted && $sendAuthInfoToErrorPage) {
            // Passthrough cookies
            $cookies = [];
            foreach ($request->getCookieParams() as $k => $v) {
                $cookies[] = rawurlencode($k) . '=' . rawurlencode($v);
            }
            $options[\GuzzleHttp\RequestOptions::HEADERS]['Cookie'] = implode('; ', $cookies);

            // Passthrough HTTP Authorization
            // 1. Get authorization header from PSR-7 request
            $authorizationHeader = $request->getHeaderLine('Authorization');
            // 2. Apache-only function
            if (!$authorizationHeader && function_exists('getallheaders')) {
                $authorizationHeader = getallheaders()['Authorization'] ?? '';
            }
            // 3. Last resort
            if (!$authorizationHeader) {
                if ($request->getServerParams()['AUTH_TYPE'] === 'Basic' && ($request->getServerParams()['PHP_AUTH_USER'] || $request->getServerParams()['PHP_AUTH_PW'])) {
                    $authorizationHeader = 'Basic ' . base64_encode($request->getServerParams()['PHP_AUTH_USER'] . ':' . $request->getServerParams()['PHP_AUTH_PW']);
                }
            }

            if ($authorizationHeader) {
                // 'Basic' authorization support
                if (strpos($authorizationHeader, 'Basic ') === 0) {
                    // Check the header value for authentication basic (only base64 characters are allowed)
                    $basicAuthorization = substr($authorizationHeader, 6);
                    if (preg_match('~[^a-zA-Z0-9+/=]~', $basicAuthorization) === 0) {
                        list ($username, $password) = explode(':', (string)base64_decode($basicAuthorization), 2);
                        $options[\GuzzleHttp\RequestOptions::AUTH] = [(string)$username, (string)$password];
                    }
                }
            }
        }

        // Disable certificate verification
        if ($this->siteConfiguration['disableCertificateVerification']) {
            $options[\GuzzleHttp\RequestOptions::VERIFY] = false;
        }

        return $options;
    }

    /**
     * @param Site $site
     */
    protected function mergeSiteConfiguration(?Site $site)
    {
        if ($site !== null) {
            if ($this->siteConfiguration === null) {
                $this->siteConfiguration = $site->getConfiguration();
            } else {
                $siteConfiguration = $site->getConfiguration();
                ArrayUtility::mergeRecursiveWithOverrule($siteConfiguration, $this->siteConfiguration);
                $this->siteConfiguration = $siteConfiguration;
            }
        }
        if (!is_array($this->siteConfiguration)) {
            $this->siteConfiguration = [];
        }
    }

    /**
     * @param UriInterface $errorPageURI
     * @param array $errorPageRequestOptions
     * @param \Throwable $e
     * @return string
     */
    protected function getDebugErrorPageRequestExceptionResponse(UriInterface $errorPageURI, array $errorPageRequestOptions, \Throwable $e): ResponseInterface
    {
        $debugArray = [
            'siteConfiguration' => $this->siteConfiguration,
            'errorHandlerConfiguration' => $this->errorHandlerConfiguration,
            'errorPageURI' => (string)$errorPageURI,
            'errorPageRequestOptions' => $errorPageRequestOptions,
            'exception' => [
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ],
        ];
        if ($e instanceof RequestException) {
            $debugArray['exception']['request'] = $e->getRequest();
            $debugArray['exception']['response'] = $e->getResponse();
        }
        if ($e->getPrevious()) {
            $debugArray['exception']['previous'] = [
                'type' => get_class($e->getPrevious()),
                'message' => $e->getPrevious()->getMessage(),
                'code' => $e->getPrevious()->getCode(),
            ];
        }
        $content = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>EXT:pagenotfoundhandling DEBUG</title>
</head>
<body>
    <h1>Exception: ' . htmlspecialchars(get_class($e)) . '</h1>
    '. \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($debugArray, 'EXT:pagenotfoundhandling DEBUG', 8, false, true, true) . '
</body>
</html>';
        return $this->createResponse($content, 500);
    }

    /**
     * @return ResponseInterface
     */
    protected function getInfiniteLoopDetectedResponse(): ResponseInterface
    {
        $content = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>508 Loop Detected</title>
</head>
<body>
    <h1>508 Loop Detected</h1>
    <p>An infinite loop has been detected.</p>
</body>
</html>';
        return $this->createResponse($content, 508, [
            'X-Error-Reason' => self::HTTP_HEADER_XERRORREASON_INFINITELOOP,
        ]);
    }

    /**
     * @return ResponseInterface
     */
    protected function createResponse(string $content, $status = 200, array $headers = []): ResponseInterface
    {
        $headers['X-Generated-By'] = self::HTTP_HEADER_XGENERATEDBY;
        return new HtmlResponse($content, $status, $headers);
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isInfiniteLoopDetectedResponse(ResponseInterface $response): bool
    {
        return
            $response->hasHeader('x-generated-by')
            && $response->getHeaderLine('x-generated-by') === self::HTTP_HEADER_XGENERATEDBY
            && $response->hasHeader('x-error-reason')
            && $response->getHeaderLine('x-error-reason') === self::HTTP_HEADER_XERRORREASON_INFINITELOOP;
    }

    /**
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(get_class($this));
    }
}
