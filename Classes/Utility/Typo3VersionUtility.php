<?php
declare(strict_types=1);
namespace AawTeam\Pagenotfoundhandling\Utility;
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

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Typo3VersionUtility
 */
class Typo3VersionUtility implements SingletonInterface
{
    /**
     * @var Typo3Version
     */
    private $typo3Version;

    /**
     *
     */
    public function __construct()
    {
        if (class_exists(Typo3Version::class)) {
            $this->typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        }
    }

    /**
     * @return string
     */
    public function getCurrentTypo3Version(): string
    {
        return $this->typo3Version ? $this->typo3Version->getVersion() : TYPO3_version;
    }

    /**
     * @param string $version
     * @return bool
     */
    public function isCurrentTypo3VersionAtLeast(string $version): bool
    {
        return $this->compareCurrentTypo3Version($version, '>=');
    }

    /**
     * @param string $version
     * @param string $operator
     * @return bool
     */
    public function compareCurrentTypo3Version(string $version, string $operator): bool
    {
        return version_compare($this->getCurrentTypo3Version(), $version, $operator);
    }
}
