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
 * Main 404 handling class
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 */
class tx_pagenotfoundhandling
{
	/**
	 * Main method called through tslib_fe::pageErrorHandler()
	 *
	 * @param array $params
	 * @param tslib_fe $tslib_fe
	 * @return string
	 * @deprecated since 2.0, will be removed in 2.2, use Tx_Pagenotfoundhandling_Controller_PagenotfoundController::main() instead
	 */
    public function main($params, tslib_fe $tslib_fe)
    {
        t3lib_div::logDeprecatedFunction();
        require_once \t3lib_extMgm::extPath('pagenotfoundhandling') . 'Classes/Controller/PagenotfoundController.php';

        $pagenotfoundController = \t3lib_div::makeInstance('Tx_Pagenotfoundhandling_Controller_PagenotfoundController');
        return $pagenotfoundController->main($params, $tslib_fe);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagenotfoundhandling/class.tx_pagenotfoundhandling.php']);
}
?>