<?php
/**
 * **************************************************************
 * Copyright notice
 *
 * (c) 2010 Agentur am Wasser | Maeder & Partner AG
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * **************************************************************
 *
 * @author     Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @copyright  Copyright (c) 2010 Agentur am Wasser | Maeder & Partner AG (http://www.agenturamwasser.ch)
 * @license    http://www.gnu.org/copyleft/gpl.html     GNU General Public License
 * @category   TYPO3
 * @package    pagenotfoundhandling
 * @version    $Id$
 */

/**
 * Language handling class
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 */

class tx_pagenotfoundhandling_LanguageSelect
{
    /**
     * Returns a select box for use in TCA userFunc
     *
     * @param array $PA
     * @param t3lib_TCEforms $fObj
     * @return string
     * @deprecated since 1.0, will be removed in 1.2, use Tx_Pagenotfoundhandling_Utility_LanguageUtility::tca() instead
     */
    public function tca($PA, t3lib_TCEforms $fObj)
    {
        \t3lib_div::logDeprecatedFunction();
        require_once \t3lib_extMgm::extPath('pagenotfoundhandling') . 'Classes/Utility/LanguageUtility.php';
        $languageUtility = \t3lib_div::makeInstance('Tx_Pagenotfoundhandling_Utility_LanguageUtility');
        return $languageUtility->tcaLanguageField($PA, $fObj);
    }

    /**
     * Returns a select box for use in constants editor userFunc
     *
     * @param array $params
     * @param $styleConfig
     * @return string
     * @deprecated since 1.0, will be removed in 1.2, use Tx_Pagenotfoundhandling_Utility_LanguageUtility::constantEditor() instead
     */
    public function constantEditor($params, $styleConfig)
    {
        \t3lib_div::logDeprecatedFunction();
        require_once \t3lib_extMgm::extPath('pagenotfoundhandling') . 'Classes/Utility/LanguageUtility.php';
        $languageUtility = \t3lib_div::makeInstance('Tx_Pagenotfoundhandling_Utility_LanguageUtility');
        return $languageUtility->constantEditor($params, $styleConfig);
    }

    /**
     * returns all visible entries from sys_language either as key=>value pairs
     * array or as array containing all rows from sys_language
     *
     * @param boolean $asPairs
     * @return array
     * @deprecated since 1.0, will be removed in 1.2, use Tx_Pagenotfoundhandling_Utility_LanguageUtility::getLanguages() instead
     */
    public static function getLanguages($asPairs = true)
    {
        \t3lib_div::logDeprecatedFunction();
        require_once \t3lib_extMgm::extPath('pagenotfoundhandling') . 'Classes/Utility/LanguageUtility.php';
        return Tx_Pagenotfoundhandling_Utility_LanguageUtility::getLanguages($asPairs);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling_LanguageSelect.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling_LanguageSelect.php']);
}
?>