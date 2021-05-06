<?php
declare(strict_types=1);
namespace AawTeam\Pagenotfoundhandling\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * History
 */
class History extends AbstractEntity
{
    /**
     * @var \DateTime
     */
    protected $time;

    /**
     * @var string
     */
    protected $siteIdentifier;

    /**
     * @var int
     */
    protected $rootpageUid = 0;

    /**
     * @var int
     */
    protected $statusCode = 0;

    /**
     * @var string
     */
    protected $failureReason;

    /**
     * @var string
     */
    protected $requestUri = '';

    /**
     * @var string
     */
    protected $refererUri = '';

    /**
     * @var string
     */
    protected $userAgent = '';

    /**
     * @return \DateTime
     */
    public function getTime(): \DateTime
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     */
    public function setTime(\DateTime $time)
    {
        $this->time = $time;
    }

    /**
     * @return string
     */
    public function getSiteIdentifier(): string
    {
        return $this->siteIdentifier;
    }

    /**
     * @param string $siteIdentifier
     */
    public function setSiteIdentifier(string $siteIdentifier)
    {
        $this->siteIdentifier = $siteIdentifier;
    }

    /**
     * @return int
     */
    public function getRootpageUid(): int
    {
        return $this->rootpageUid;
    }

    /**
     * @param int $rootpageUid
     */
    public function setRootpageUid(int $rootpageUid)
    {
        $this->rootpageUid = $rootpageUid;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    /**
     * @param string $failureReason
     */
    public function setFailureReason(?string $failureReason)
    {
        $this->failureReason = $failureReason;
    }

    /**
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * @param string $requestUri
     */
    public function setRequestUri(string $requestUri)
    {
        $this->requestUri = $requestUri;
    }

    /**
     * @return string
     */
    public function getRefererUri(): string
    {
        return $this->refererUri;
    }

    /**
     * @param string $refererUri
     */
    public function setRefererUri(string $refererUri)
    {
        $this->refererUri = $refererUri;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }
}
