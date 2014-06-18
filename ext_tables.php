<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


if (version_compare(TYPO3_version, '6.0', '<') || \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 6002001) {
    require_once t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Overrides/TcaAdditions.php';
}

