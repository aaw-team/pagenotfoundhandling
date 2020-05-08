<?php
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

defined('TYPO3_MODE') or die();

$bootstrap = function() {
    /** @var \AawTeam\Pagenotfoundhandling\Configuration\ExtensionConfiguration $extensionConfiguration */
    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AawTeam\Pagenotfoundhandling\Configuration\ExtensionConfiguration::class);

    // Register logger
    if ($extensionConfiguration->has('logLevel')) {
        $logLevel = (int)$extensionConfiguration->get('logLevel');
        if ($logLevel > -1 && $logLevel < 8) {
            // TYPO3 is PSR-3 compliant as of v10, see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-88799-IntroducedPSR-3CompatibleLoggingAPI.html
            // For now, just 'translate' the log level numbers.
            // @todo update this code when dropping support for TYPO3 < v10
            /** @var \AawTeam\Pagenotfoundhandling\Utility\Typo3VersionUtility $typo3VersionUtility */
            $typo3VersionUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AawTeam\Pagenotfoundhandling\Utility\Typo3VersionUtility::class);
            if ($typo3VersionUtility->isCurrentTypo3VersionAtLeast('10')) {
                $typo3LogLevels2psr3Levels = [
                    0 => \Psr\Log\LogLevel::EMERGENCY,
                    1 => \Psr\Log\LogLevel::ALERT,
                    2 => \Psr\Log\LogLevel::CRITICAL,
                    3 => \Psr\Log\LogLevel::ERROR,
                    4 => \Psr\Log\LogLevel::WARNING,
                    5 => \Psr\Log\LogLevel::NOTICE,
                    6 => \Psr\Log\LogLevel::INFO,
                    7 => \Psr\Log\LogLevel::DEBUG,
                ];
                if (array_key_exists($logLevel, $typo3LogLevels2psr3Levels)) {
                    $logLevel = $typo3LogLevels2psr3Levels[$logLevel];
                }
            }

            $GLOBALS['TYPO3_CONF_VARS']['LOG']['AawTeam']['Pagenotfoundhandling']['writerConfiguration'] = [
                $logLevel => [
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        'logFileInfix' => 'pnfh',
                    ],
                ],
            ];
        }
    }

    // Add the statistics backend module configuration
    if ($extensionConfiguration->has('enableStatisticsModule') && $extensionConfiguration->get('enableStatisticsModule')) {
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            'pagenotfoundhandling-module-statistics',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            [
                'source' => 'EXT:pagenotfoundhandling/Resources/Public/Icons/ModuleStatistics.svg'
            ]
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
module.tx_pagenotfoundhandling {
    view {
        templateRootPaths.10 = EXT:pagenotfoundhandling/Resources/Private/Backend/Templates/
        partialRootPaths.10 = EXT:pagenotfoundhandling/Resources/Private/Backend/Partials/
        layoutRootPaths.10 = EXT:pagenotfoundhandling/Resources/Private/Backend/Layouts/
    }
}'
        );
    }
};
$bootstrap();
unset($bootstrap);
