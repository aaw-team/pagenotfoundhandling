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

$EM_CONF[$_EXTKEY] = [
    'title' => 'Error Handler for TYPO3',
    'description' => 'A versatile Error Handler for the TYPO3 CMS Site Handling',
    'category' => 'fe',
    'author' => 'Agentur am Wasser | Maeder & Partner AG',
    'author_email' => 'development@agenturamwasser.ch',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '4.0.0-dev',
    'constraints' => [
        'depends' => [
            'php' => '7.2',
            'typo3' => '9.5.99-10.3.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
