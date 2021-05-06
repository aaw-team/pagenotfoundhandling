<?php
declare(strict_types=1);
namespace AawTeam\Pagenotfoundhandling\Domain\Model\Dto;

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
 * StatisticsFilterForm
 */
class StatisticsFilterForm
{
    /**
     * @var \DateTime
     */
    protected $dateTimeStart;

    /**
     * @var \DateTime
     */
    protected $dateTimeStop;

    /**
     * @return \DateTime
     */
    public function getDateTimeStart(): ?\DateTime
    {
        return $this->dateTimeStart;
    }

    /**
     * @param \DateTime $dateTimeStart
     */
    public function setDateTimeStart(?\DateTime $dateTimeStart)
    {
        $this->dateTimeStart = $dateTimeStart;
    }

    /**
     * @return \DateTime
     */
    public function getDateTimeStop(): ?\DateTime
    {
        return $this->dateTimeStop;
    }

    /**
     * @param \DateTime $dateTimeStop
     */
    public function setDateTimeStop(?\DateTime $dateTimeStop)
    {
        $this->dateTimeStop = $dateTimeStop;
    }
}
