<?php
namespace AawTeam\Pagenotfoundhandling\Realurl;

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
 * Realurl XCLASS
 *
 * It is used for realurl versions >=1.12.8 and <2.0, thus it's here for
 * compatibility reasons.
 *
 * As of realurl v2.0, \AawTeam\Pagenotfoundhandling\Realurl\Decoder\UrlDecoder is
 * used for the very same job.
 *
 * @author   Agentur am Wasser | Maeder & Partner AG <development@agenturamwasser.ch>
 * @category TYPO3
 * @package  pagenotfoundhandling
 * @see      \AawTeam\Pagenotfoundhandling\Realurl\Decoder\UrlDecoder
 * @deprecated since pagenotfoundhandling v3, will be removed in pagenotfoundhandling v4.0.
 */
class RealurlV1 extends \tx_realurl
{
    /**
     * @param string $msg
     * @return void
     * @see \tx_realurl::decodeSpURL_throw404()
     */
    public function decodeSpURL_throw404($msg)
    {
        // Set language to allow localized error pages
        if ($this->detectedLanguage > 0) {
            $_GET['L'] = $this->detectedLanguage;
        }

        return parent::decodeSpURL_throw404($msg);
    }
}
