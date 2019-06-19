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

if (!defined ('TYPO3_MODE')) {
     die ('Access denied.');
}


$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pagenotfoundhandling']);

// add the fields to tca of sys_domain
if(!isset($conf['disableDomainConfig']) || empty($conf['disableDomainConfig'])) {

    $tempColumns = array (
        'tx_pagenotfoundhandling_enable' => array(
            'onChange' => 'reload',
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.enable',
            'config' => array (
                'type' => 'check',
                'default' => '0',
            )
        ),
        'tx_pagenotfoundhandling_default404Page' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default404Page',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'wizards' => array(
                    'suggest' => array('type' => 'suggest')
                ),
            )
        ),
        'tx_pagenotfoundhandling_defaultTemplateFile' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.defaultTemplateFile',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'html,htm,tmpl,txt',
                'uploadfolder' => 'uploads/tx_pagenotfoundhandling',
                'show_thumbs' => 1,
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            )
        ),
        'tx_pagenotfoundhandling_default403Page' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Page',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'wizards' => array(
                    'suggest' => array('type' => 'suggest')
                ),
            )
        ),
        'tx_pagenotfoundhandling_default403Header' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Header',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'select',
                'items' => array(
                    array('LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Header.none', -1),
                    array('LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.default403Header.default', 0),
                    array('HTTP/1.1 400 Bad Request', 1),
                    array('HTTP/1.1 401 Unauthorized', 2),
                    array('HTTP/1.1 402 Payment Required', 3),
                    array('HTTP/1.1 403 Forbidden', 4),
                ),
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'tx_pagenotfoundhandling_ignoreLanguage' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.ignoreLanguage',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'check',
                'default' => '0',
            )
        ),
        'tx_pagenotfoundhandling_forceLanguage' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.forceLanguage',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'size' => 1,
                'autoSizeMax' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'allowed' => 'sys_language',
                'wizards' => array(
                    'suggest' => array('type' => 'suggest'),
                ),
            ),
        ),
        'tx_pagenotfoundhandling_languageParam' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.languageParam',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'input',
                'size' => '5',
                'default' => 'L',
            )
        ),
        'tx_pagenotfoundhandling_passthroughContentTypeHeader' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.passthroughContentTypeHeader',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'check',
                'default' => '0',
            )
        ),
        'tx_pagenotfoundhandling_sendXForwardedForHeader' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.sendXForwardedForHeader',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'check',
                'default' => '0',
            )
        ),
        'tx_pagenotfoundhandling_additionalHeaders' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.additionalHeaders',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'input',
                'default' => '',
            )
        ),
        'tx_pagenotfoundhandling_digestAuthentication' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.digestAuthentication',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'input',
                'default' => '',
            )
        ),
    );

    // avoid deprecation messages
    // @todo: integrate this above, when dropping support for TYPO3 < 7
    if (version_compare(TYPO3_version, '7', '>=')) {
        $tempColumns['tx_pagenotfoundhandling_default403Header']['config']['renderType'] = 'selectSingle';
    }

    // force-enable dividers2Tabs
    $GLOBALS['TCA']['sys_domain']['ctrl']['dividers2tabs'] = 1;

    // inject field tx_pagenotfoundhandling_enable to 'requestUpdate'
    if (version_compare(TYPO3_version, '8', '<')) {
        if($GLOBALS['TCA']['sys_domain']['ctrl']['requestUpdate']) {
            $GLOBALS['TCA']['sys_domain']['ctrl']['requestUpdate'] .= ',tx_pagenotfoundhandling_enable';
        } else {
            $GLOBALS['TCA']['sys_domain']['ctrl']['requestUpdate'] = 'tx_pagenotfoundhandling_enable';
        }
    }

    // add the columns
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_domain', $tempColumns);

    // define palettes
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_main'] = array(
        'showitem' => 'tx_pagenotfoundhandling_default404Page,--linebreak--,tx_pagenotfoundhandling_defaultTemplateFile',
        'canNotCollapse' => true,
    );
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_forbidden'] = array(
        'showitem' => 'tx_pagenotfoundhandling_default403Page,--linebreak--,tx_pagenotfoundhandling_default403Header',
        'canNotCollapse' => true,
    );
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_lang'] = array(
        'showitem' => 'tx_pagenotfoundhandling_ignoreLanguage,tx_pagenotfoundhandling_languageParam,--linebreak--,tx_pagenotfoundhandling_forceLanguage',
        'canNotCollapse' => true,
    );
    $GLOBALS['TCA']['sys_domain']['palettes']['pagenotfoundhandling_palette_opts'] = array(
        'showitem' => 'tx_pagenotfoundhandling_passthroughContentTypeHeader,tx_pagenotfoundhandling_sendXForwardedForHeader,--linebreak--,tx_pagenotfoundhandling_additionalHeaders,--linebreak--,tx_pagenotfoundhandling_digestAuthentication',
        'canNotCollapse' => true,
    );

    // add types
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_domain', '
        --div--;LLL:EXT:pagenotfoundhandling/Resources/Private/Language/locallang_db.xml:pagenotfoundhandling.sys_domain.tcasheet.title,
        tx_pagenotfoundhandling_enable,
        --palette--;;pagenotfoundhandling_palette_main,
        --palette--;;pagenotfoundhandling_palette_forbidden,
        --palette--;;pagenotfoundhandling_palette_lang,
        --palette--;;pagenotfoundhandling_palette_opts');
}
