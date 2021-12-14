<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Controller;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Christoph Daecke <typo3@mediadreams.org>
 */

use DERHANSEN\SfEventMgt\Domain\Repository\LocationRepository;
use DERHANSEN\SfEventMgt\Domain\Repository\OrganisatorRepository;
use DERHANSEN\SfEventMgt\Domain\Repository\SpeakerRepository;
use GeorgRinger\NumberedPagination\NumberedPagination;
use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use Mediadreams\MdEventmgtFrontend\Domain\Repository\EventRepository;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 * @package Mediadreams\MdEventmgtFrontend\Controller
 */
abstract class AbstractController extends ActionController
{
    /**
     * Frontend user data
     *
     * @var array $feUser
     */
    protected $feUser = null;

    /**
     * categoryRepository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * eventRepository
     *
     * @var EventRepository
     */
    protected $eventRepository = null;

    /**
     * locationRepository
     *
     * @var LocationRepository
     */
    protected $locationRepository = null;

    /**
     * organisatorRepository
     *
     * @var OrganisatorRepository
     */
    protected $organisatorRepository = null;

    /**
     * speakerRepository
     *
     * @var SpeakerRepository
     */
    protected $speakerRepository = null;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param EventRepository $eventRepository
     */
    public function injectEventRepository(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param LocationRepository $locationRepository
     */
    public function injectLocationRepository(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param OrganisatorRepository $organisatorRepository
     */
    public function injectOrganisatorRepository(OrganisatorRepository $organisatorRepository)
    {
        $this->organisatorRepository = $organisatorRepository;
    }

    /**
     * @param SpeakerRepository $speakerRepository
     */
    public function injectSpeakerRepository(SpeakerRepository $speakerRepository)
    {
        $this->speakerRepository = $speakerRepository;
    }

    /**
     * Deactivate errorFlashMessage
     *
     * @return bool|string
     */
    public function getErrorFlashMessage()
    {
        return LocalizationUtility::translate('controller.error', 'md_eventmgt_frontend');
    }

    /**
     * Action initialize
     *
     * Check, if frontend user is logged in
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function initializeAction(): void
    {
        parent::initializeAction();

        $this->feUser = $GLOBALS['TSFE']->fe_user->user;

        if (!$this->feUser && $this->actionMethodName != 'accessAction') {
            $this->redirect('access');
        }

        if ($this->actionMethodName == 'createAction' || $this->actionMethodName == 'updateAction') {
            // set configuration for date
            $this->arguments['event']
                ->getPropertyMappingConfiguration()
                ->forProperty('startdate')
                ->setTypeConverterOption(
                    DateTimeConverter::class,
                    DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                    $this->settings['dateFormat'][$this->settings['selectedDateFormat']]['format']
                );

            if ($this->request->hasArgument('event')) {
                if (empty($this->request->getArgument('event')['enddate'])) {
                    $this->arguments->getArgument('event')->getPropertyMappingConfiguration()->skipProperties('enddate');
                } else {
                    $this->arguments['event']
                        ->getPropertyMappingConfiguration()
                        ->forProperty('enddate')
                        ->setTypeConverterOption(
                            DateTimeConverter::class,
                            DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                            $this->settings['dateFormat'][$this->settings['selectedDateFormat']]['format']
                        );
                }
            }
        }
    }

    /**
     * Assign additional data to template
     */
    protected function assignAdditionalData(): void
    {
        if (isset($this->settings['parentCategory']) && strlen($this->settings['parentCategory']) > 0) {
            $this->categoryRepository->setDefaultOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
            $categories = $this->categoryRepository->findByParent($this->settings['parentCategory']);
            $this->view->assign('categories', $categories);
        }

        if (isset($this->settings['locationStoragePid']) && strlen($this->settings['locationStoragePid']) > 0) {
            $this->locationRepository->setDefaultOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
            $locations = $this->locationRepository->findByPid($this->settings['locationStoragePid']);
            $this->view->assign('locations', $locations);
        }

        if (isset($this->settings['organisatorStoragePid']) && strlen($this->settings['organisatorStoragePid']) > 0) {
            $this->organisatorRepository->setDefaultOrderings(['name' => QueryInterface::ORDER_ASCENDING]);
            $organisators = $this->organisatorRepository->findByPid($this->settings['organisatorStoragePid']);
            $this->view->assign('organisators', $organisators);
        }

        if (isset($this->settings['speakerStoragePid']) && strlen($this->settings['speakerStoragePid']) > 0) {
            $this->speakerRepository->setDefaultOrderings(['name' => QueryInterface::ORDER_ASCENDING]);
            $speakers = $this->speakerRepository->findByPid($this->settings['speakerStoragePid']);
            $this->view->assign('speakers', $speakers);
        }

        if (isset($this->settings['relatedStoragePid']) && strlen($this->settings['relatedStoragePid']) > 0) {
            $this->eventRepository->setDefaultOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
            $relatedEvents = $this->eventRepository->findByPid($this->settings['relatedStoragePid']);
            $this->view->assign('relatedEvents', $relatedEvents);
        }
    }

    /**
     * Assign pagination to current view object
     *
     * @param QueryResultInterface $queryResult
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function assignPagination(QueryResultInterface $queryResult): void
    {
        $paginateConfig = $this->settings['paginate'] ?? [];
        $itemsPerPage = (int)($paginateConfig['itemsPerPage'] ?: 10);
        $maximumNumberOfLinks = (int)($paginateConfig['maximumNumberOfLinks'] ?: 6);

        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;

        $paginator = new QueryResultPaginator(
            $queryResult,
            $currentPage,
            $itemsPerPage
        );

        $pagination = new NumberedPagination(
            $paginator,
            $maximumNumberOfLinks
        );

        $this->view->assign('pagination', ['paginator' => $paginator, 'pagination' => $pagination]);
    }

    /**
     * @param Event $event
     */
    protected function setTime(Event $event): void
    {
        if ($this->settings['selectedDateFormat'] == 'allDay') {
            $event->getStartdate()->setTime(0, 0, 0, 0);

            if ($event->getEnddate()) {
                $event->getEnddate()->setTime(0, 0, 0, 0);
            }
        }
    }
}
