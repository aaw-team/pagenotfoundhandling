<?php
declare(strict_types=1);
namespace AawTeam\Pagenotfoundhandling\Configuration;
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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as TYPO3ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ExtensionConfiguration
 *
 * This class serves as a wrapper for the TYPO3 ExtensionConfiguration API
 * introduced in v9.
 */
class ExtensionConfiguration implements SingletonInterface
{
    /**
     * @param string $path
     * @return bool
     */
    public function has(string $path): bool
    {
        try {
            $this->getTypo3ExtensionConfiguration()->get('pagenotfoundhandling', $path);
        } catch (ExtensionConfigurationPathDoesNotExistException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param string|null $path
     * @return mixed
     */
    public function get(?string $path = null)
    {
        if ($path === null) {
            return $this->getTypo3ExtensionConfiguration()->get('pagenotfoundhandling');
        }
        return $this->getTypo3ExtensionConfiguration()->get('pagenotfoundhandling', $path);
    }

    /**
     * @return TYPO3ExtensionConfiguration
     */
    private function getTypo3ExtensionConfiguration(): TYPO3ExtensionConfiguration
    {
        return GeneralUtility::makeInstance(TYPO3ExtensionConfiguration::class);
    }
}
