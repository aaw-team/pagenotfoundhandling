<?php
namespace AawTeam\Pagenotfoundhandling\Realurl\Decoder;

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


/**
 * Realurl UrlDecoder XCLASS
 *
 * It is used for realurl versions >=2.0 (and <2.0.12).
 *
 * For older realurl versions, \AawTeam\Pagenotfoundhandling\Realurl\RealurlV1 is
 * used for the very same job.
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 * @see      \AawTeam\Pagenotfoundhandling\Realurl\RealurlV1
 * @deprecated since pagenotfoundhandling v3, will be removed in pagenotfoundhandling v4.0.
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
