<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// register pageNotFound_handling
$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'] = 'USER_FUNCTION:Aaw\\Pagenotfoundhandling\\Controller\\PagenotfoundController->main';
