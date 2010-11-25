<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'] = 'USER_FUNCTION:EXT:pagenotfoundhandling/class.tx_pagenotfoundhandling.php:tx_pagenotfoundhandling->main';
?>