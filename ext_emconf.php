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
    'title' => '404 Page not found handling',
    'description' => 'Highly configurable 404 page handling. Supports multi domain systems with multiple languages.',
    'category' => 'fe',
    'author' => 'Agentur am Wasser | Maeder & Partner AG',
    'author_email' => 'development@agenturamwasser.ch',
    'state' => 'stable',
    'modify_tables' => 'sys_domain',
    'clearCacheOnLoad' => 1,
    'version' => '3.0.2-dev',
    'constraints' => [
        'depends' => [
            'php' => '7.1.0',
            'typo3' => '8.7.26-10.3.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'realurl' => '2.2.0',
        ],
    ],
];
