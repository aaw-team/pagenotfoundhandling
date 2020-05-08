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

    // Add the statistics backend module
    if ($extensionConfiguration->has('enableStatisticsModule') && $extensionConfiguration->get('enableStatisticsModule')) {
        /** @var \AawTeam\Pagenotfoundhandling\Utility\Typo3VersionUtility $typo3VersionUtility */
        $typo3VersionUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AawTeam\Pagenotfoundhandling\Utility\Typo3VersionUtility::class);
        if ($typo3VersionUtility->isCurrentTypo3VersionAtLeast('10')) {
            $extensionName = 'Pagenotfoundhandling';
            $controllerActions= [
                \AawTeam\Pagenotfoundhandling\Controller\StatisticsController::class => 'index',
            ];
        } else {
            $extensionName = 'AawTeam.Pagenotfoundhandling';
            $controllerActions= [
                'Statistics' => 'index',
            ];
        }
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            $extensionName,
            'web',
            'statistics',
            '',
            $controllerActions,
            [
                'access' => 'user,group',
                'iconIdentifier' => 'pagenotfoundhandling-module-statistics',
                'labels' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/module_statistics.xlf',
                'navigationComponentId' => '',
                'inheritNavigationComponentFromMainModule' => false,
            ]
        );
    }
};
$bootstrap();
unset($bootstrap);
