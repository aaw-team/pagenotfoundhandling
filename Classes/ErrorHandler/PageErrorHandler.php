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

use AawTeam\Pagenotfoundhandling\Utility\StatisticsUtility;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
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
    protected const HTTP_HEADER_XERRORREASON_INVALIDORNOSITE = 'Invalid or no Site object found';

    /**
     * @var int
     */
    protected $statusCode = 0;

    /**
     * @var array
     */
    protected $errorHandlerConfiguration = [];

    /**
     * @param int $statusCode
     * @param array $errorHandlerConfiguration
     */
    public function __construct(int $statusCode, array $errorHandlerConfiguration)
    {
        $this->statusCode = $statusCode;
        $this->errorHandlerConfiguration = $errorHandlerConfiguration;
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

        // Note: at the moment, we only support the TYPO3 built-in Site object
        if (!($site instanceof Site)) {
            $this->getLogger()->error(
                ($site === null ? 'No Site object found' : 'Invalid Site object found'),
                [
                    'requestURI' => (string)$request->getUri(),
                    'referer' => $request->getServerParams()['HTTP_REFERER'],
                ]
            );
            return $this->getInvalidOrNoSiteResponse();
        }

        // Record the request
        if (!$site->getConfiguration()['disableStatisticsRecording']) {
            StatisticsUtility::recordRequest($request, $this->statusCode, $reasons['code'] ?? null);
        }

        $this->getLogger()->debug('Startup', [
            'site' => $site->getIdentifier(),
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
                if ($site->getConfiguration()['debugErrorPageRequestException']) {
                    // Return a response with debug content
                    return $this->getDebugErrorPageRequestExceptionResponse($errorPageURI, $errorPageRequestOptions, $site, $e);
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
        if ($site->getConfiguration()['passthroughContentTypeHeader'] && $errorPageResponse->hasHeader('content-type')) {
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
        /** @var \TYPO3\CMS\Core\Site\Entity\Site $site */
        $site = $request->getAttribute('site', null);

        // Analyze error page
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $urlParams = $linkService->resolve($this->errorHandlerConfiguration['errorPage']);
        if ($urlParams['type'] !== 'page') {
            throw new \InvalidArgumentException('errorPage must be a TYPO3 page URL t3://page..');
        }

        // Build additional GET params
        $queryString = '';
        if ($site->getConfiguration()['additionalGetParams']) {
            $queryString .= '&' . trim($site->getConfiguration()['additionalGetParams'], '&');
        }
        if ($this->errorHandlerConfiguration['additionalGetParams']) {
            $queryString .= '&' . trim($this->errorHandlerConfiguration['additionalGetParams'], '&');
        }
        if (strpos($queryString, '###CURRENT_URL###') !== false) {
            $queryString = str_replace('###CURRENT_URL###', (string)$request->getUri(), $queryString);
        }
        // Setup query parameters
        $requestUriParameters = [];
        parse_str($queryString, $requestUriParameters);
        // Remove reserved names from query string
        $requestUriParameters = array_filter($requestUriParameters, function($key) {
            return !in_array(strtolower($key), ['id', 'chash', 'l', 'mp']);
        }, ARRAY_FILTER_USE_KEY);

        // Determine language to request:
        // 1. Force a language
        // 2. Use currently requested language
        // 3. Fallback to default language
        $language = null;
        if ($site->getConfiguration()['forceLanguage'] > -1) {
            try {
                $language = $site->getLanguageById($site->getConfiguration()['forceLanguage']);
            } catch (\InvalidArgumentException $e) {
                if ($e->getCode() !== 1522960188) {
                    throw $e;
                }
            }
        } elseif ($site->getConfiguration()['forceLanguage'] == -1) {
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
        return $requestUri;
    }

    /**
     * @param ServerRequestInterface $request
     * @param UriInterface $errorPageURI
     * @return array
     */
    protected function generateErrorPageRequestOptions(ServerRequestInterface $request, UriInterface $errorPageURI): array
    {
        /** @var \TYPO3\CMS\Core\Site\Entity\Site $site */
        $site = $request->getAttribute('site', null);

        // Compose request options
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'User-Agent' => $request->getServerParams()['HTTP_USER_AGENT'] ?? 'TYPO3 EXT:pagenotfoundhandling',
                'Referer' => $request->getUri()->__toString(),
            ],
        ];
        // Override default timeout
        if ($site->getConfiguration()['requestTimeout'] > 0) {
            $options[\GuzzleHttp\RequestOptions::TIMEOUT] = $site->getConfiguration()['requestTimeout'];
        } elseif ($GLOBALS['TYPO3_CONF_VARS']['HTTP'][\GuzzleHttp\RequestOptions::TIMEOUT] < 1) {
            // Force a 30 sec timeout, when none is set at all
            $options[\GuzzleHttp\RequestOptions::TIMEOUT] = 30;
        }
        // Override default connect_timeout
        if ($site->getConfiguration()['connectTimeout'] > 0) {
            $options[\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT] = $site->getConfiguration()['connectTimeout'];
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
        $currentRequestIsTrusted = GeneralUtility::getIndpEnv('TYPO3_SSL') || $site->getConfiguration()['trustInsecureIncomingConnections'];
        $sendAuthInfoToErrorPage = $errorPageURI->getScheme() === 'https' || $site->getConfiguration()['passAuthinfoToInsecureConnections'];

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
            if (!$authorizationHeader && isset($request->getServerParams()['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $request->getServerParams()['HTTP_AUTHORIZATION'];
            }
            if (!$authorizationHeader && isset($request->getServerParams()['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $request->getServerParams()['REDIRECT_HTTP_AUTHORIZATION'];
            }
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
        if ($site->getConfiguration()['disableCertificateVerification']) {
            $options[\GuzzleHttp\RequestOptions::VERIFY] = false;
        }

        return $options;
    }

    /**
     * @param UriInterface $errorPageURI
     * @param array $errorPageRequestOptions
     * @param Site $site
     * @param \Throwable $e
     * @return ResponseInterface
     */
    protected function getDebugErrorPageRequestExceptionResponse(UriInterface $errorPageURI, array $errorPageRequestOptions, Site $site, \Throwable $e): ResponseInterface
    {
        $debugArray = [
            'siteConfiguration' => $site->getConfiguration(),
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
    protected function getInvalidOrNoSiteResponse(): ResponseInterface
    {
        $content = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>500 Internal Server Error</title>
</head>
<body>
    <h1>500 Internal Server Error</h1>
    <p>Invalid or no Site object found.</p>
</body>
</html>';
        return $this->createResponse($content, 500, [
            'X-Error-Reason' => self::HTTP_HEADER_XERRORREASON_INVALIDORNOSITE,
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
