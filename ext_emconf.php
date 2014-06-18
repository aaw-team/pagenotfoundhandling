<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "pagenotfoundhandling".
 *
 * Auto generated 07-04-2014 18:51
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
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'sys_domain',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => 'Agentur am Wasser | Maeder & Partner AG',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			//'static_info_tables' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"ChangeLog";s:4:"2d5b";s:33:"class.tx_pagenotfoundhandling.php";s:4:"f05f";s:48:"class.tx_pagenotfoundhandling_LanguageSelect.php";s:4:"2872";s:21:"ext_conf_template.txt";s:4:"5a0c";s:12:"ext_icon.gif";s:4:"9d5d";s:17:"ext_localconf.php";s:4:"da82";s:14:"ext_tables.php";s:4:"e4f2";s:14:"ext_tables.sql";s:4:"aaab";s:17:"locallang_404.xml";s:4:"fd81";s:16:"locallang_db.xml";s:4:"2b2b";s:10:"README.txt";s:4:"42ba";s:14:"doc/manual.sxw";s:4:"79ca";s:24:"res/defaultTemplate.tmpl";s:4:"c28a";}',
	'suggests' => array(
	),
);

?>