<?php
namespace Aaw\Pagenotfoundhandling\Realurl\Decoder;

/**
 * **************************************************************
 * Copyright notice
 *
 * (c) 2016 Agentur am Wasser | Maeder & Partner AG
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
 * @copyright  Copyright (c) 2016 Agentur am Wasser | Maeder & Partner AG (http://www.agenturamwasser.ch)
 * @license    http://www.gnu.org/copyleft/gpl.html     GNU General Public License
 * @category   TYPO3
 * @package    pagenotfoundhandling
 */


/**
 * Realurl UrlDecoder XCLASS
 *
 * It is used for realurl versions >=2.0 (and <2.0.12).
 *
 * For older realurl versions, \Aaw\Pagenotfoundhandling\Realurl\RealurlV1 is
 * used for the very same job.
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 * @see      \Aaw\Pagenotfoundhandling\Realurl\RealurlV1
 */
class UrlDecoder extends \DmitryDulepov\Realurl\Decoder\UrlDecoder
{
    /**
     * @param string $errorMessage
     * @return void
     * @see \DmitryDulepov\Realurl\Decoder\UrlDecoder::throw404()
     */
    protected function throw404($errorMessage)
    {
        // Set language to allow localized error pages
        $_GET['L'] = $this->detectedLanguageId;

        return parent::throw404($errorMessage);
    }
}
