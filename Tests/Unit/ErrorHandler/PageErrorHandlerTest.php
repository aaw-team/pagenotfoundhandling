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

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\Uri;

/**
 * PageErrorHandlerTest
 */
class PageErrorHandlerTest extends UnitTestCase
{
    protected function getTestSubject(): PageErrorHandler
    {
        $subject = new PageErrorHandler(404, []);
        return $subject;
    }

    /**
     * @test
     */
    public function implementsCorrectInterface()
    {
        self::assertInstanceOf(PageErrorHandlerInterface::class, $this->getTestSubject());
    }

    /**
     * @test
     */
    public function detectAndHandleInfiniteLoops()
    {
        $queryParams = [
            'loopPrevention' => '1',
        ];
        $uri = (new Uri('https://example.org/'))->withQuery(http_build_query($queryParams));
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->method('getQueryParams')->willReturn($queryParams);
        $serverRequest->method('getUri')->willReturn($uri);
        $serverRequest->method('getServerParams')->willReturn(['HTTP_REFERER' => '']);

        $response = $this->getTestSubject()->handlePageError($serverRequest, 'Unit testing');
        self::assertEquals(508, $response->getStatusCode());
        self::assertTrue($response->hasHeader('X-Error-Reason'));
        self::assertNotEmpty($response->getHeaderLine('X-Error-Reason'));
    }

    /**
     * @test
     */
    public function noSiteObjectLeadsToServerError()
    {
        $queryParams = [
            'loopPrevention' => '0',
        ];
        $uri = (new Uri('https://example.org/'))->withQuery(http_build_query($queryParams));
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->method('getQueryParams')->willReturn($queryParams);
        $serverRequest->method('getUri')->willReturn($uri);
        $serverRequest->method('getServerParams')->willReturn(['HTTP_REFERER' => '']);

        $serverRequest->method('getAttribute')->with('site')->willReturn(null);

        $response = $this->getTestSubject()->handlePageError($serverRequest, 'Unit testing');
        self::assertEquals(500, $response->getStatusCode());
        self::assertTrue($response->hasHeader('X-Error-Reason'));
        self::assertNotEmpty($response->getHeaderLine('X-Error-Reason'));
    }
}
