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

$bootstrap = function(string $extKey) {
    // Load extension configuration
    if (version_compare(TYPO3_version, '9.0', '<')){
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey], ['allowed_classes' => false]);
    } else {
        $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get($extKey);
    }

    if (is_array($extConf) && $extConf['enableStatisticsModule']) {
        // Add the statistics backend module
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'AawTeam.Pagenotfoundhandling',
            'web',
            'statistics',
            '',
            [
                'Statistics' => 'index',
            ],
            [
                'access' => 'user,group',
                // @todo add icon
                //'iconIdentifier' => '',
                'labels' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/module_statistics.xlf',
                'navigationComponentId' => '',
                'inheritNavigationComponentFromMainModule' => false,
            ]
        );
    }
};
$bootstrap($_EXTKEY);
unset($bootstrap);
