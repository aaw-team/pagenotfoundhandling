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
     */
    public function tca($PA, t3lib_TCEforms $fObj)
    {
        $options = $this->_getLanguageSelector($PA['itemFormElValue']);

        if(empty($options)) {
            $return = 'No languages found';
        } else {
            $return = '<select id="' . $PA['itemFormElID'] . '" name="' . $PA['itemFormElName'] . '">' . $options . '</select>';
        }

        return $return;
    }

    /**
     * Returns a select box for use in constants editor userFunc
     *
     * @param array $params
     * @param t3lib_tsStyleConfig $styleConfig
     * @return string
     */
    public function constantEditor($params, t3lib_tsStyleConfig $styleConfig)
    {
        $params['fieldName'];
        $params['fieldValue'];

        $options = $this->_getLanguageSelector($params['fieldValue']);

        if(empty($options)) {
            $return = 'No languages found';
        } else {
            $return = '<select name="' . $params['fieldName'] . '">' . $options . '</select>';
        }

        return $return;
    }

    /**
     * Returns all sys_languages as options for use in a select tag
     *
     * @param int $selectedUid
     * @return string
     */
    protected function _getLanguageSelector($selectedUid = 0)
    {
        $selectedUid = (int) $selectedUid;
        $noneSelected = true;
        $options = '<option value="0"></option>';

        $languages = self::getLanguages(false);
        foreach($languages as $language) {
            $selected = '';
            if($selectedUid == $language['uid']) {
                $selected = 'selected="selected"';
                $noneSelected = false;
            }

            $options .= sprintf('<option %s value="%s">%s [PID:%s]</option>',
                                    $selected,
                                    $language['uid'],
                                    $language['title'],
                                    $language['pid']);
        }

        if(!empty($selectedUid) && $noneSelected) {
            $options = '<option selected="selected" value="">Illegal value: [' . $selectedUid . ']</option>' . $options;
        }
        return $options;
    }

    /**
     * returns all visible entries from sys_language either as key=>value pairs
     * array or as array containing all rows from sys_language
     *
     * @param boolean $asPairs
     * @return array
     */
    public static function getLanguages($asPairs = true)
    {
        $return = array();
        $languages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'sys_language', 'hidden=0', '', 'uid ASC, pid ASC');

        if($asPairs) {
            foreach($languages as $language) {
                $return[$language['uid']] = $language['title'];
            }
        } else {
            $return = $languages;
        }

        return $return;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling_LanguageSelect.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling_LanguageSelect.php']);
}
?>