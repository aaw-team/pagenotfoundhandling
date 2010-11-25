<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

// add the fields to tca of sys_domain
if(!isset($conf['disableDomainConfig']) || empty($conf['disableDomainConfig'])) {

    $tempColumns = array (
        'tx_pagenotfoundhandling_enable' => array(
            'onChange' => 'reload',
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/locallang_db.xml:pagenotfoundhandling.sys_domain.enable',
            'config' => array (
                'type' => 'check',
                'default' => '0',
            )
        ),
        'tx_pagenotfoundhandling_default404Page' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/locallang_db.xml:pagenotfoundhandling.sys_domain.default404Page',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            )
        ),
        'tx_pagenotfoundhandling_defaultTemplateFile' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/locallang_db.xml:pagenotfoundhandling.sys_domain.defaultTemplateFile',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'html,htm,tmpl,txt',
                'uploadfolder' => 'uploads/tx_pagenotfoundhandling',
                'show_thumbs' => 1,
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            )
        ),
        'tx_pagenotfoundhandling_ignoreLanguage' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/locallang_db.xml:pagenotfoundhandling.sys_domain.ignoreLanguage',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array (
                'type' => 'check',
                'default' => '0',
            )
        ),
        'tx_pagenotfoundhandling_forceLanguage' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pagenotfoundhandling/locallang_db.xml:pagenotfoundhandling.sys_domain.forceLanguage',
            'displayCond' => 'FIELD:tx_pagenotfoundhandling_enable:REQ:true',
            'config' => array(
                'type' => 'user',
                'userFunc' => 'EXT:pagenotfoundhandling/class.tx_pagenotfoundhandling_LanguageSelect.php:tx_pagenotfoundhandling_LanguageSelect->tca',
            ),
        ),
    );

    $GLOBALS['TCA']['sys_domain']['ctrl']['dividers2tabs'] = 1;
    if($GLOBALS['TCA']['sys_domain']['ctrl']['requestUpdate']) {
        $GLOBALS['TCA']['sys_domain']['ctrl']['requestUpdate'] .= ',tx_pagenotfoundhandling_enable';
    } else {
        $GLOBALS['TCA']['sys_domain']['ctrl']['requestUpdate'] = 'tx_pagenotfoundhandling_enable';
    }

    t3lib_extMgm::addTCAcolumns('sys_domain', $tempColumns, 1);
    t3lib_extMgm::addToAllTCAtypes('sys_domain', '
        --div--;LLL:EXT:pagenotfoundhandling/locallang_db.xml:pagenotfoundhandling.sys_domain.tcasheet.title,
        tx_pagenotfoundhandling_enable;;;;1-1-1,
        tx_pagenotfoundhandling_default404Page;;;;1-1-1,
        tx_pagenotfoundhandling_defaultTemplateFile;;;;1-1-1,
        tx_pagenotfoundhandling_ignoreLanguage;;;;1-1-1,
        tx_pagenotfoundhandling_forceLanguage;;;;1-1-1');


}
?>