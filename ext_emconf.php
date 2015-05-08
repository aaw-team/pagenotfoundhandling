<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "pagenotfoundhandling".
 *
 * Auto generated 23-06-2014 17:53
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => '404 Page not found handling',
	'description' => 'Highly configurable 404 page handling. Supports multi domain systems with multiple languages.',
	'category' => 'fe',
	'author' => 'Christian Futterlieb',
	'author_email' => 'development@agenturamwasser.ch',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'sys_domain',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => 'Agentur am Wasser | Maeder & Partner AG',
	'version' => '2.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:25:{s:9:"ChangeLog";s:4:"c1dc";s:33:"class.tx_pagenotfoundhandling.php";s:4:"a0f7";s:48:"class.tx_pagenotfoundhandling_LanguageSelect.php";s:4:"0b5d";s:16:"ext_autoload.php";s:4:"307a";s:21:"ext_conf_template.txt";s:4:"e8dc";s:12:"ext_icon.gif";s:4:"9d5d";s:17:"ext_localconf.php";s:4:"2664";s:14:"ext_tables.php";s:4:"d3f7";s:14:"ext_tables.sql";s:4:"37d9";s:17:"locallang_404.xml";s:4:"fd81";s:16:"locallang_db.xml";s:4:"2a3b";s:10:"README.txt";s:4:"407e";s:45:"Classes/Controller/PagenotfoundController.php";s:4:"be32";s:35:"Classes/Utility/LanguageUtility.php";s:4:"6770";s:44:"Configuration/TCA/Overrides/TcaAdditions.php";s:4:"5d0c";s:26:"Documentation/Includes.txt";s:4:"6d5f";s:23:"Documentation/Index.rst";s:4:"15fd";s:38:"Documentation/Administration/Index.rst";s:4:"c146";s:37:"Documentation/Configuration/Index.rst";s:4:"4312";s:50:"Documentation/Configuration/DomainRecord/Index.rst";s:4:"683c";s:54:"Documentation/Configuration/ExtensionManager/Index.rst";s:4:"a296";s:36:"Documentation/Introduction/Index.rst";s:4:"16b2";s:37:"Documentation/KnownProblems/Index.rst";s:4:"7ecd";s:40:"Resources/Private/Templates/default.html";s:4:"de6b";s:24:"res/defaultTemplate.tmpl";s:4:"c28a";}',
	'suggests' => array(
	),
);

?>