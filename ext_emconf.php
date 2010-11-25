<?php

########################################################################
# Extension Manager/Repository config file for ext "pagenotfoundhandling".
#
# Auto generated 25-11-2010 12:27
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

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
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'static_info_tables' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"ChangeLog";s:4:"ff5f";s:10:"README.txt";s:4:"42ba";s:33:"class.tx_pagenotfoundhandling.php";s:4:"fcf4";s:48:"class.tx_pagenotfoundhandling_LanguageSelect.php";s:4:"029a";s:21:"ext_conf_template.txt";s:4:"b3c2";s:12:"ext_icon.gif";s:4:"9d5d";s:17:"ext_localconf.php";s:4:"da82";s:14:"ext_tables.php";s:4:"2fc0";s:14:"ext_tables.sql";s:4:"c182";s:17:"locallang_404.xml";s:4:"f263";s:16:"locallang_db.xml";s:4:"de7a";s:14:"doc/manual.sxw";s:4:"9555";s:24:"res/defaultTemplate.tmpl";s:4:"7f5e";}',
	'suggests' => array(
	),
);

?>