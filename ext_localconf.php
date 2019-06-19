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
    // register pageNotFound_handling
    $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'] = 'USER_FUNCTION:AawTeam\\Pagenotfoundhandling\\Controller\\PagenotfoundController->main';

    // Load extension configuration
    if (version_compare(TYPO3_version, '9.0', '<')){
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey], ['allowed_classes' => false]);
    } else {
        $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get($extKey);
    }

    // Register logger
    if (is_array($extConf) && array_key_exists('logLevel', $extConf)) {
        $logLevel = (int)$extConf['logLevel'];
        if ($logLevel > -1 && $logLevel < 8) {
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['AawTeam']['Pagenotfoundhandling']['writerConfiguration'] = [
                $logLevel => [
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        'logFileInfix' => 'pnfh',
                    ],
                ],
            ];
        }
    }
};
$bootstrap($_EXTKEY);
unset($bootstrap);
