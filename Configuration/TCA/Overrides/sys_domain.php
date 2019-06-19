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

// Load extension configuration
if (version_compare(TYPO3_version, '9.0', '<')){
    $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pagenotfoundhandling'], ['allowed_classes' => false]);
} else {
    $conf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get('pagenotfoundhandling');
}

// add the fields to tca of sys_domain
if(!isset($conf['disableDomainConfig']) || empty($conf['disableDomainConfig'])) {

    $tempColumns = [
        'tx_pagenotfoundhandling_enable' => [
            'onChange' => 'reload',
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.enable',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ]
        ],
        'tx_pagenotfoundhandling_default404Page' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default404Page',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'wizards' => [
                    'suggest' => ['type' => 'suggest']
                ],
            ]
        ],
        'tx_pagenotfoundhandling_defaultTemplateFile' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.defaultTemplateFile',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'html,htm,tmpl,txt',
                'uploadfolder' => 'uploads/tx_pagenotfoundhandling',
                'show_thumbs' => 1,
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ]
        ],
        'tx_pagenotfoundhandling_default403Page' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Page',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'wizards' => [
                    'suggest' => ['type' => 'suggest']
                ],
            ]
        ],
        'tx_pagenotfoundhandling_default403Header' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Header',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Header.none', -1],
                    ['LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Header.default', 0],
                    ['HTTP/1.1 400 Bad Request', 1],
                    ['HTTP/1.1 401 Unauthorized', 2],
                    ['HTTP/1.1 402 Payment Required', 3],
                    ['HTTP/1.1 403 Forbidden', 4],
                ],
                'size' => 1,
                'maxitems' => 1,
            ]
        ],
        'tx_pagenotfoundhandling_ignoreLanguage' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.ignoreLanguage',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ]
        ],
        'tx_pagenotfoundhandling_forceLanguage' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.forceLanguage',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'autoSizeMax' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'allowed' => 'sys_language',
                'wizards' => [
                    'suggest' => ['type' => 'suggest'],
                ],
            ],
        ],
        'tx_pagenotfoundhandling_languageParam' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.languageParam',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => '5',
                'default' => 'L',
            ]
        ],
        'tx_pagenotfoundhandling_passthroughContentTypeHeader' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.passthroughContentTypeHeader',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ]
        ],
        'tx_pagenotfoundhandling_sendXForwardedForHeader' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.sendXForwardedForHeader',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ]
        ],
        'tx_pagenotfoundhandling_additionalHeaders' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.additionalHeaders',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'input',
                'default' => '',
            ]
        ],
        'tx_pagenotfoundhandling_digestAuthentication' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.digestAuthentication',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => [
                'type' => 'input',
                'default' => '',
            ]
        ],
    ];

    // add the columns
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_domain', $tempColumns);

    // define palettes
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_main'] = [
        'showitem' => 'tx_pagenotfoundhandling_default404Page,--linebreak--,tx_pagenotfoundhandling_defaultTemplateFile',
    ];
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_forbidden'] = [
        'showitem' => 'tx_pagenotfoundhandling_default403Page,--linebreak--,tx_pagenotfoundhandling_default403Header',
    ];
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_lang'] = [
        'showitem' => 'tx_pagenotfoundhandling_ignoreLanguage,tx_pagenotfoundhandling_languageParam,--linebreak--,tx_pagenotfoundhandling_forceLanguage',
    ];
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_opts'] = [
        'showitem' => 'tx_pagenotfoundhandling_passthroughContentTypeHeader,tx_pagenotfoundhandling_sendXForwardedForHeader,--linebreak--,tx_pagenotfoundhandling_additionalHeaders,--linebreak--,tx_pagenotfoundhandling_digestAuthentication',
    ];

    // add types
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_domain', '
        --div--;LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.tcasheet.title,
        tx_pagenotfoundhandling_enable,
        --palette--;;pagenotfoundhandling_palette_main,
        --palette--;;pagenotfoundhandling_palette_forbidden,
        --palette--;;pagenotfoundhandling_palette_lang,
        --palette--;;pagenotfoundhandling_palette_opts');
}
