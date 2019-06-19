<?php
namespace AawTeam\Pagenotfoundhandling\Utility;

/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Language utility
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 * @deprecated since pagenotfoundhandling v3, will be removed in pagenotfoundhandling v4.0.
 */
class LanguageUtility
{
    /**
     * Returns a select box for use in constants editor userFunc
     *
     * @param array $params
     * @param $styleConfig
     * @return string
     */
    public function constantEditor($params, $styleConfig)
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
     * Returns all visible entries from sys_language either as key=>value pairs
     * array or as array containing all rows from sys_language
     *
     * @param boolean $asPairs
     * @return array
     */
    public static function getLanguages($asPairs = true)
    {
        $return = [];
        $languages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_language')->select(
            ['*'],
            'sys_language',
            [
                'hidden' => 0,
            ]
        )->fetchAll();
        if (is_array($languages)) {
            if ($asPairs) {
                foreach ($languages as $language) {
                    $return[$language['uid']] = $language['title'];
                }
            } else {
                $return = $languages;
            }
        }

        return $return;
    }
}
