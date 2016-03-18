<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// register pageNotFound_handling
$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'] = 'USER_FUNCTION:Aaw\\Pagenotfoundhandling\\Controller\\PagenotfoundController->main';

// Register an XCLASS for the realurl UrlDecoder
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
    $packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Package\\PackageManager');
    //$packageManager instanceof \TYPO3\CMS\Core\Package\PackageManager;
    $realurlVersion = $packageManager->getPackage('realurl')->getPackageMetaData()->getVersion();
    if(version_compare($realurlVersion, '2.0') >= 0) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['DmitryDulepov\\Realurl\\Decoder\\UrlDecoder'] = array(
            'className' => 'Aaw\\Pagenotfoundhandling\\Realurl\\Decoder\\UrlDecoder',
        );
    } elseif(version_compare($realurlVersion, '1.12.8') >= 0) {
        // version 1.12.8 was the first realurl version with official TYPO3 6.2 support
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['tx_realurl'] = array(
            'className' => 'Aaw\\Pagenotfoundhandling\\Realurl\\RealurlV1',
        );
    }
}
