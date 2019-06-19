<?php
declare(strict_types=1);
namespace AawTeam\Pagenotfoundhandling\Controller;

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

use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * StatisticsController
 */
class StatisticsController extends ActionController
{
    /**
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     *
     */
    protected function indexAction()
    {
        $this->view->assignMultiple([
            'requestUris' => $this->getHistoryRecordsGroupedBy('request_uri'),
            'refererUris' => $this->getHistoryRecordsGroupedBy('referer_uri'),
            'statusCodes' => $this->getHistoryRecordsGroupedBy('status_code', 5),
        ]);
    }

    /**
     * @param string $groupByField
     * @param int $limit
     * @return array
     */
    protected function getHistoryRecordsGroupedBy(string $groupByField, int $limit = 10): array
    {
        $qb = $this->getConnectionForTable('tx_pagenotfoundhandling_history')->createQueryBuilder();
        $qb->selectLiteral(...[
            'count(*) AS ' . $qb->quoteIdentifier('count'),
            'MAX('. $qb->quoteIdentifier('history.time') . ') AS '. $qb->quoteIdentifier('latest_time'),
        ])
        ->addSelect(
            'history.' . $groupByField
        )
        ->from('tx_pagenotfoundhandling_history', 'history')
        ->groupBy('history.' . $groupByField)
        ->orderBy('count', 'DESC')
        ->addOrderBy('history.' . $groupByField)
        ->setMaxResults($limit)
        ;
        return $qb->execute()->fetchAll();
    }

    /**
     * @param string|null $tableName
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    protected function getConnectionForTable(?string $tableName = null)
    {
        if ($tableName === null) {
            return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        }
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
    }
}
