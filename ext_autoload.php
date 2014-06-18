<?php
/**
 * **************************************************************
 * Copyright notice
 *
 * (c) 2014 Agentur am Wasser | Maeder & Partner AG
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
 * @copyright  Copyright (c) 2014 Agentur am Wasser | Maeder & Partner AG (http://www.agenturamwasser.ch)
 * @license    http://www.gnu.org/copyleft/gpl.html     GNU General Public License
 * @category   TYPO3
 * @package    pagenotfoundhandling
 * @version    $Id$
 */

if (version_compare(TYPO3_version, '4.7', '>=')) {
    return array();
}

$extPath = t3lib_extMgm::extPath('pagenotfoundhandling');
return array(
    'tx_pagenotfoundhandling_controller_pagenotfoundcontroller' => $extPath . 'Classes/Controller/PagenotfoundController.php',
    'tx_pagenotfoundhandling_utility_languageutility' => $extPath . 'Classes/Utility/LanguageUtility.php',
);