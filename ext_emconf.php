<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "pagenotfoundhandling".
 *
 * Auto generated 19-06-2015 16:29
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => '404 Page not found handling',
    'description' => 'Highly configurable 404 page handling. Supports multi domain systems with multiple languages.',
    'category' => 'fe',
    'author' => 'Agentur am Wasser | Maeder & Partner AG',
    'author_email' => 'development@agenturamwasser.ch',
    'state' => 'stable',
    'modify_tables' => 'sys_domain',
    'clearCacheOnLoad' => 1,
    'version' => '2.5.1-dev',
    'constraints' => array(
        'depends' => array(
            'php' => '7.0.0',
            'typo3' => '8.7.26-9.5.99',
        ),
        'conflicts' => array(),
        'suggests' => array(
            'realurl' => '1.12.8'
        )
    )
);
