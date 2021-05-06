<?php
declare(strict_types=1);
namespace AawTeam\Pagenotfoundhandling\Domain\Repository;

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

use AawTeam\Pagenotfoundhandling\Domain\Model\Dto\StatisticsFilterForm;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder ;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * StatisticsRepository
 */
class HistoryRepository extends Repository
{
    /**
     * @var string
     */
    protected $rootPageUid;

    /**
     * @param int $rootPageUid
     */
    public function setRootPageUid(int $rootPageUid)
    {
        $this->rootPageUid = $rootPageUid;
    }

    /**
     * Make this repository read-only
     */
    public function update($modifiedObject)
    {
        throw new \RuntimeException('The error handler history is read-only');
    }

    /**
     * Make this repository read-only
     */
    public function add($object)
    {
        throw new \RuntimeException('The error handler history is read-only');
    }

    /**
     * Make this repository read-only
     */
    public function remove($object)
    {
        throw new \RuntimeException('The error handler history is read-only');
    }

    /**
     * Make this repository read-only
     */
    public function removeAll()
    {
        throw new \RuntimeException('The error handler history is read-only');
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findGroupedRequestUriByFilterForm(StatisticsFilterForm $statisticsFilterForm, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilderForGroupedQuery('request_uri');

        // Apply filters
        $this->addConstraintsFromStatisticsFilterForm($qb, $statisticsFilterForm);

        // Pagination
        $qb->setMaxResults($limit)->setFirstResult($offset);

        return $qb->execute()->fetchAll();
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     * @return int
     */
    public function countGroupedRequestUriByFilterForm(StatisticsFilterForm $statisticsFilterForm): int
    {
        $qb = $this->createQueryBuilderForGroupedQuery('request_uri');

        // Apply filters
        $this->addConstraintsFromStatisticsFilterForm($qb, $statisticsFilterForm);

        return $this->getRowcountFromQuery($qb);
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findGroupedRefererByFilterForm(StatisticsFilterForm $statisticsFilterForm, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilderForGroupedQuery('referer_uri');

        // Apply filters
        $this->addConstraintsFromStatisticsFilterForm($qb, $statisticsFilterForm);

        // Pagination
        $qb->setMaxResults($limit)->setFirstResult($offset);

        return $qb->execute()->fetchAll();
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     * @return int
     */
    public function countGroupedRefererByFilterForm(StatisticsFilterForm $statisticsFilterForm): int
    {
        $qb = $this->createQueryBuilderForGroupedQuery('referer_uri');

        // Apply filters
        $this->addConstraintsFromStatisticsFilterForm($qb, $statisticsFilterForm);

        return $this->getRowcountFromQuery($qb);
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findGroupedStatusCodeByFilterForm(StatisticsFilterForm $statisticsFilterForm, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilderForGroupedQuery('status_code');

        // Apply filters
        $this->addConstraintsFromStatisticsFilterForm($qb, $statisticsFilterForm);

        // Pagination
        $qb->setMaxResults($limit)->setFirstResult($offset);

        return $qb->execute()->fetchAll();
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     * @return int
     */
    public function countGroupedStatusByFilterForm(StatisticsFilterForm $statisticsFilterForm): int
    {
        $qb = $this->createQueryBuilderForGroupedQuery('status_code');

        // Apply filters
        $this->addConstraintsFromStatisticsFilterForm($qb, $statisticsFilterForm);

        return $this->getRowcountFromQuery($qb);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return int
     */
    protected function getRowcountFromQuery(QueryBuilder $queryBuilder): int
    {
        // Create query
        $countQueryBuilder = $this->getConnectionForTable()->createQueryBuilder();

        $countQueryBuilder->getConcreteQueryBuilder()
        ->select('COUNT(*) AS ' . $queryBuilder->quoteIdentifier('rowcount'))
        ->from('(' . $queryBuilder->getSQL() . ')', $countQueryBuilder->quoteIdentifier('t'))
        ->setParameters($queryBuilder->getParameters(), $queryBuilder->getParameterTypes());

        return $countQueryBuilder->execute()->fetch()['rowcount'];
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param StatisticsFilterForm $statisticsFilterForm
     */
    protected function addConstraintsFromStatisticsFilterForm(QueryBuilder $queryBuilder, StatisticsFilterForm $statisticsFilterForm)
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte('time', $queryBuilder->createNamedParameter($statisticsFilterForm->getDateTimeStart()->getTimestamp(), \PDO::PARAM_INT)),
                $queryBuilder->expr()->lt('time', $queryBuilder->createNamedParameter($statisticsFilterForm->getDateTimeStop()->getTimestamp(), \PDO::PARAM_INT))
            )
        );
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilderForGroupedQuery(string $groupByField): QueryBuilder
    {
        $qb = $this->getConnectionForTable('tx_pagenotfoundhandling_history')->createQueryBuilder();
        $qb->selectLiteral(...[
            'COUNT(*) AS ' . $qb->quoteIdentifier('count'),
            'MAX('. $qb->quoteIdentifier('history.time') . ') AS '. $qb->quoteIdentifier('latest_time'),
            $qb->quoteIdentifier('history.' . $groupByField)
        ])
        ->from('tx_pagenotfoundhandling_history', 'history')
        ->groupBy('history.' . $groupByField)
        ->orderBy('count', 'DESC')
        ->addOrderBy('history.' . $groupByField);

        if ($this->rootPageUid) {
            $qb->where($qb->expr()->eq('rootpage_uid', $qb->createNamedParameter($this->rootPageUid, \PDO::PARAM_INT)));
        }

        return $qb;
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
