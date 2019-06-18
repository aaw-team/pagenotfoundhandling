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

// Add new columns to 'site'
$siteColumns = [
    'additionalGetParams' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.additionalGetParams',
        'config' => [
            'type' => 'input',
        ],
    ],
    'passthroughContentTypeHeader' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.passthroughContentTypeHeader',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
    ],
    'requestTimeout' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.requestTimeout',
        'description' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.requestTimeout.description',
        'config' => [
            'type' => 'input',
            'eval' => 'int',
            'range' => [
                'lower' => 0,
            ],
            'default' => 0,
            'size' => 8,
        ]
    ],
    'connectTimeout' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.connectTimeout',
        'description' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.connectTimeout.description',
        'config' => [
            'type' => 'input',
            'eval' => 'int',
            'range' => [
                'lower' => 0,
            ],
            'default' => 0,
            'size' => 8,
        ]
    ],
    'forceLanguage' => [
        // @todo: see site_language.typo3Language
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.forceLanguage',
        'description' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.forceLanguage.description',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'special' => 'languages',
            'items' => [
                [
                    'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.forceLanguage.disable',
                    -1,
                ],
            ],
        ],
    ],
    'trustInsecureIncomingConnections' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.trustInsecureIncomingConnections',
        'description' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.trustInsecureIncomingConnections.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
    ],
    'passAuthinfoToInsecureConnections' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.passAuthinfoToInsecureConnections',
        'description' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.passAuthinfoToInsecureConnections.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
    ],
    'disableCertificateVerification' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.disableCertificateVerification',
        'description' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.disableCertificateVerification.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
    ],
    'debugErrorPageRequestException' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.debugErrorPageRequestException',
        'description' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.debugErrorPageRequestException.description',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns'] = array_merge($GLOBALS['SiteConfiguration']['site']['columns'], $siteColumns);
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = preg_replace(
    '~,\\s*errorHandling\\s*,~',
    ', errorHandling,
    --div--;LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.tabs.pnfh,
        --palette--;LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.palettes.pnfh-fetching;pnfh-fetching,
        --palette--;LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.palettes.pnfh-responding;pnfh-responding,
        --palette--;LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site.palettes.pnfh-language;pnfh-language,
    ',
    $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
);
// Palettes
$GLOBALS['SiteConfiguration']['site']['palettes']['pnfh-fetching'] = [
    'showitem' => '
        additionalGetParams, --linebreak--,
        requestTimeout, connectTimeout, --linebreak--,
        passAuthinfoToInsecureConnections, trustInsecureIncomingConnections, --linebreak--,
        disableCertificateVerification, debugErrorPageRequestException,
    ',
];
$GLOBALS['SiteConfiguration']['site']['palettes']['pnfh-responding'] = [
    'showitem' => '
        passthroughContentTypeHeader,
    ',
];
$GLOBALS['SiteConfiguration']['site']['palettes']['pnfh-language'] = [
    'showitem' => '
        ignoreLanguage, forceLanguage
    ',
];

// Add new columns to 'site_errorhandling'
$siteErrorHandlingColumns = [
    'errorPage' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site_errorhandling.errorPage',
        'displayCond' => 'FIELD:errorPhpClassFQCN:=:' . \AawTeam\Pagenotfoundhandling\ErrorHandler\PageErrorHandler::class,
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
            'eval' => 'required',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'blindLinkOptions' => 'url,file,mail,spec,folder',
                        'blindLinkFields' => 'class,params,target,title',
                    ],
                ],
            ],
        ],
    ],
    'additionalGetParams' => [
        'label' => 'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site_errorhandling.additionalGetParams',
        'displayCond' => 'FIELD:errorPhpClassFQCN:=:' . \AawTeam\Pagenotfoundhandling\ErrorHandler\PageErrorHandler::class,
        'config' => [
            'type' => 'input',
        ],
    ],
];
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns'] = array_merge($GLOBALS['SiteConfiguration']['site_errorhandling']['columns'], $siteErrorHandlingColumns);
$GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem'] = str_replace(
    'errorPhpClassFQCN',
    'errorPhpClassFQCN, errorPage, --palette--;LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site_errorhandling.palettes.pnfh-advanced;pnfh-advanced,
',
    $GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem']
);
$GLOBALS['SiteConfiguration']['site_errorhandling']['palettes']['pnfh-advanced'] = [
    'showitem' => '
        additionalGetParams
    ',
];

// Add a valuePicker to field errorPhpClassFQCN
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['errorPhpClassFQCN']['onChange'] = 'reload';
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['errorPhpClassFQCN']['config']['valuePicker'] = [
    'items' => [
        [
            'LLL:EXT:pagenotfoundhandling/Resources/Private/Language/backend.xlf:site_errorhandling.errorPhpClassFQCN.valuePicker.pnfh',
            \AawTeam\Pagenotfoundhandling\ErrorHandler\PageErrorHandler::class,
        ],
    ],
];
