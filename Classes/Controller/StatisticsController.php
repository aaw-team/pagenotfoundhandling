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

use AawTeam\Pagenotfoundhandling\Domain\Model\Dto\StatisticsFilterForm;
use AawTeam\Pagenotfoundhandling\Domain\Repository\HistoryRepository;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\ViewHelpers\Be\InfoboxViewHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use AawTeam\Pagenotfoundhandling\Domain\Model\History;

/**
 * StatisticsController
 */
class StatisticsController extends ActionController
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;

    /**
     * @var HistoryRepository
     */
    protected $historyRepository;

    /**
     * @param HistoryRepository $historyRepository
     */
    public function injectHistoryRepository(HistoryRepository $historyRepository)
    {
        $this->historyRepository = $historyRepository;
    }

    /**
     * {@inheritDoc}
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::initializeAction()
     */
    protected function initializeAction()
    {
        // @todo: do this with dependency injection, as soon as
        // a) this gets finally defined in TYPO3 and
        // b) support for TYPO3 < v10 is removed
        $this->serverRequest = $GLOBALS['TYPO3_REQUEST'];

        /** @var \TYPO3\CMS\Core\Site\Entity\SiteInterface $site */
        $site = $this->serverRequest->getAttribute('site');
        $this->historyRepository->setRootPageUid($site->getRootPageId());
    }

    /**
     * {@inheritDoc}
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::initializeView()
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $this->view->assign('rootPage', BackendUtility::getRecord('pages', $this->serverRequest->getAttribute('site')->getRootPageId()));
    }

    /**
     * @param string $message
     * @param string $title
     * @param int $state
     */
    protected function infoBoxAction(string $message, string $title = '', int $state = InfoboxViewHelper::STATE_NOTICE)
    {
        $this->view->assignMultiple([
            'infoBox' => [
                'message' => $message,
                'title' => $title,
                'state' => $state,
            ]
        ]);
    }

    /**
     *
     */
    protected function indexAction()
    {
        $statisticsFilterForm = $this->loadStatisticsFilterFormFromSession();

        $this->view->assignMultiple([
            'statisticsFilterForm' => $statisticsFilterForm,
            'requestUris' => $this->historyRepository->findGroupedRequestUriByFilterForm($statisticsFilterForm, 5),
            'refererUris' => $this->historyRepository->findGroupedRefererByFilterForm($statisticsFilterForm, 5),
            'statusCodes' => $this->historyRepository->findGroupedStatusCodeByFilterForm($statisticsFilterForm, 5),
        ]);
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     * @param string $returnToAction
     */
    protected function persistStatisticsFilterAction(StatisticsFilterForm $statisticsFilterForm, string $returnToAction = null)
    {
        $this->persistStatisticsFilterFormInSession($statisticsFilterForm);
        if ($returnToAction === null) {
            $returnToAction = 'index';
        }
        $this->redirect($returnToAction);
    }

    /**
     * @param string $returnToAction
     */
    protected function resetStatisticsFilterAction(string $returnToAction = null)
    {
        $this->getBackendUserAuthentication()->setAndSaveSessionData('statisticsFilterForm', null);
        $this->addFlashMessage('Filter has been successfully reset.');
        if ($returnToAction === null) {
            $returnToAction = 'index';
        }
        $this->redirect($returnToAction);
    }

    /**
     * @param int $page
     */
    protected function listRequestUriAction(int $page = 1)
    {
        $statisticsFilterForm = $this->loadStatisticsFilterFormFromSession();
        $pagination = $this->generatePaginationInfoArray($page, $this->historyRepository->countGroupedRequestUriByFilterForm($statisticsFilterForm));

        $this->view->assignMultiple([
            'pagination' => $pagination,
            'statisticsFilterForm' => $statisticsFilterForm,
            'requestUris' => $this->historyRepository->findGroupedRequestUriByFilterForm($statisticsFilterForm, $pagination['itemsPerPage'], $pagination['offset']),
        ]);
    }

    /**
     * @param int $page
     */
    protected function listRefererAction(int $page = 1)
    {
        $statisticsFilterForm = $this->loadStatisticsFilterFormFromSession();
        $pagination = $this->generatePaginationInfoArray($page, $this->historyRepository->countGroupedRefererByFilterForm($statisticsFilterForm));

        $this->view->assignMultiple([
            'pagination' => $pagination,
            'statisticsFilterForm' => $statisticsFilterForm,
            'referers' => $this->historyRepository->findGroupedRefererByFilterForm($statisticsFilterForm, $pagination['itemsPerPage'], $pagination['offset']),
        ]);
    }

    /**
     * @param int $page
     */
    protected function listStatusAction(int $page = 1)
    {
        $statisticsFilterForm = $this->loadStatisticsFilterFormFromSession();
        $pagination = $this->generatePaginationInfoArray($page, $this->historyRepository->countGroupedStatusByFilterForm($statisticsFilterForm));

        $this->view->assignMultiple([
            'pagination' => $pagination,
            'statisticsFilterForm' => $statisticsFilterForm,
            'statusCodes' => $this->historyRepository->findGroupedStatusCodeByFilterForm($statisticsFilterForm, $pagination['itemsPerPage'], $pagination['offset']),
        ]);
    }

    /**
     * @param string $requestUri
     */
    protected function detailRequestUriAction(string $requestUri)
    {
        $statisticsFilterForm = $this->loadStatisticsFilterFormFromSession();

        $this->view->assignMultiple([
            'statisticsFilterForm' => $statisticsFilterForm,
            'requestUri' => $requestUri,
        ]);
    }



    /* ----- */

    /**
     * @param int $page
     * @param int $totalItems
     * @return array
     */
    protected function generatePaginationInfoArray(int $page, int $totalItems): array
    {
        $itemsPerPage = (int)$this->settings['pagination']['itemsPerPage'] ?? 7;
        $maximumNumberOfLinks = (int)$this->settings['pagination']['maximumNumberOfLinks'] ?? 7;

        $displayRangeStart = 1;
        $totalPages = ($totalItems > $itemsPerPage)
            ? ceil($totalItems / $itemsPerPage)
            : 1;

        $offset = 0;
        $currentPage = min([max([1, $page]), $totalPages]);
        if ($currentPage > 1) {
            $offset = (int)(($currentPage - 1) * $itemsPerPage);
            $displayRangeStart = max([$currentPage - floor($maximumNumberOfLinks/2), 1]);
        }

        $displayRangeEnd = $displayRangeStart + $maximumNumberOfLinks - 1;
        if ($displayRangeEnd > $totalPages) {
            $displayRangeEnd = $totalPages;
            $displayRangeStart = max([$displayRangeEnd - $maximumNumberOfLinks, 1]);
        }

        $pages = [];
        for ($i = $displayRangeStart; $i <= $displayRangeEnd; $i++) {
            $pages[] = [
                'number' => $i,
                'isCurrent' => $currentPage == $i,
            ];
        }

        return [
            // DB query
            'offset' => $offset,
            'itemsPerPage' => $itemsPerPage,

            // Presentation
            'pages' => $pages,
            'numberOfPages' => $totalPages,
            'previousPage' => $currentPage > 1 ? ($currentPage - 1) : null,
            'nextPage' => $currentPage < $totalPages ? ($currentPage + 1) : null,
            'firstPage' => 0,
            'lastPage' => $totalPages,
            'current' => $currentPage,
            'displayRangeStart' => $displayRangeStart,
            'displayRangeEnd' => $displayRangeEnd,
            'hasMorePages' => $displayRangeEnd + 1 < $totalPages,
            'hasLessPages' => $displayRangeStart - 1 > 1,
        ];
    }

    /**
     * @return StatisticsFilterForm
     */
    protected function loadStatisticsFilterFormFromSession(): StatisticsFilterForm
    {
        /** @var StatisticsFilterForm $statisticsFilterForm */
        $statisticsFilterForm = $this->objectManager->get(StatisticsFilterForm::class);

        $sessionDataString = $this->getBackendUserAuthentication()->getSessionData('statisticsFilterForm');
        if (is_string($sessionDataString)) {
            $sessionDataArray = json_decode($sessionDataString, true);
            if (is_array($sessionDataArray)) {
                if (array_key_exists('dateTimeStart', $sessionDataArray) && is_string($sessionDataArray['dateTimeStart'])) {
                    $dateTimeStart = \DateTime::createFromFormat(\DateTime::ISO8601, $sessionDataArray['dateTimeStart']);
                    if ($dateTimeStart instanceof \DateTime) {
                        $statisticsFilterForm->setDateTimeStart($dateTimeStart);
                    }
                }
                if (array_key_exists('dateTimeStop', $sessionDataArray) && is_string($sessionDataArray['dateTimeStop'])) {
                    $dateTimeStop = \DateTime::createFromFormat(\DateTime::ISO8601, $sessionDataArray['dateTimeStop']);
                    if ($dateTimeStop instanceof \DateTime) {
                        $statisticsFilterForm->setDateTimeStop($dateTimeStop);
                    }
                }
            }
        }

        if (!$statisticsFilterForm->getDateTimeStart()) {
            $statisticsFilterForm->setDateTimeStart(new \DateTime('today -1 month'));
        }
        if (!$statisticsFilterForm->getDateTimeStop()) {
            $statisticsFilterForm->setDateTimeStop(new \DateTime('today'));
        }

        return $statisticsFilterForm;
    }

    /**
     * @param StatisticsFilterForm $statisticsFilterForm
     */
    protected function persistStatisticsFilterFormInSession(StatisticsFilterForm $statisticsFilterForm)
    {
        $this->getBackendUserAuthentication()->setAndSaveSessionData('statisticsFilterForm', json_encode([
            'dateTimeStart' => $statisticsFilterForm->getDateTimeStart()->format(\DateTime::ISO8601),
            'dateTimeStop' => $statisticsFilterForm->getDateTimeStop()->format(\DateTime::ISO8601),
        ]));
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
