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
use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use Mediadreams\MdEventmgtFrontend\Domain\Repository\CategoryRepository;
use Mediadreams\MdEventmgtFrontend\Domain\Repository\EventRepository;
use Mediadreams\MdEventmgtFrontend\Service\EmailService;
use Mediadreams\MdEventmgtFrontend\TypeConverter\FloatConverter;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
     * @var array FeUser array
     */
    protected array $feUser = [];

    protected CategoryRepository $categoryRepository;
    protected EmailService $emailService;
    protected EventRepository $eventRepository;
    protected FloatConverter $floatConverter;
    protected LocationRepository $locationRepository;
    protected OrganisatorRepository $organisatorRepository;
    protected SpeakerRepository $speakerRepository;

    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function injectEmailService(EmailService $emailService): void
    {
        $this->emailService = $emailService;
    }

    public function injectEventRepository(EventRepository $eventRepository): void
    {
        $this->eventRepository = $eventRepository;
    }

    public function injectFloatConverter(FloatConverter $floatConverter): void
    {
        $this->floatConverter = $floatConverter;
    }

    public function injectLocationRepository(LocationRepository $locationRepository): void
    {
        $this->locationRepository = $locationRepository;
    }

    public function injectOrganisatorRepository(OrganisatorRepository $organisatorRepository): void
    {
        $this->organisatorRepository = $organisatorRepository;
    }

    public function injectSpeakerRepository(SpeakerRepository $speakerRepository): void
    {
        $this->speakerRepository = $speakerRepository;
    }

    /**
     * Deactivate errorFlashMessage
     *
     * @return bool|string
     */
    public function getErrorFlashMessage(): bool|string
    {
        return false;
    }

    /**
     * Action initialize
     *
     * Check, if frontend user is logged in and set typeConverter for `startdate` and `enddate`
     * @throws PropagateResponseException
     */
    public function initializeAction(): void
    {
        parent::initializeAction();

        $this->feUser = $this->request->getAttribute('frontend.user')->user ?? [];

        if (count($this->feUser) == 0 && $this->actionMethodName != 'accessAction') {
            $uri = $this->uriBuilder->uriFor('access');
            $response = $this->responseFactory->createResponse()
                ->withHeader('Location', $uri);

            throw new PropagateResponseException($response, 307);
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
     * Add some additional data to view
     */
    protected function initializeView(): void
    {
        $this->view->assignMultiple([
            'feUser' => $this->feUser,
            'contentObjectData' => $this->request->getAttribute('currentContentObject')->data,
            'pageData' => $this->request->getAttribute('frontend.page.information')->getPageRecord()
        ]);

        if (isset($this->settings['parentCategory']) && $this->settings['parentCategory'] > 0) {
            $this->categoryRepository->setDefaultOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
            $categories = $this->categoryRepository->findBy(['parent' => $this->settings['parentCategory']]);
            $this->view->assign('categories', $categories);
        }

        if (isset($this->settings['locationStoragePid']) && $this->settings['locationStoragePid'] > 0) {
            $this->locationRepository->setDefaultOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
            $locations = $this->locationRepository->findBy(['pid' => $this->settings['locationStoragePid']]);
            $this->view->assign('locations', $locations);
        }

        if (isset($this->settings['organisatorStoragePid']) && $this->settings['organisatorStoragePid'] > 0) {
            $this->organisatorRepository->setDefaultOrderings(['name' => QueryInterface::ORDER_ASCENDING]);
            $organisators = $this->organisatorRepository->findBy(['pid' => $this->settings['organisatorStoragePid']]);
            $this->view->assign('organisators', $organisators);
        }

        if (isset($this->settings['speakerStoragePid']) && $this->settings['speakerStoragePid'] > 0) {
            $this->speakerRepository->setDefaultOrderings(['name' => QueryInterface::ORDER_ASCENDING]);
            $speakers = $this->speakerRepository->findBy(['pid' => $this->settings['speakerStoragePid']]);
            $this->view->assign('speakers', $speakers);
        }

        if (isset($this->settings['relatedStoragePid']) && $this->settings['relatedStoragePid'] > 0) {
            $this->eventRepository->setDefaultOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
            $relatedEvents = $this->eventRepository->findBy(['pid' => $this->settings['relatedStoragePid']]);
            $this->view->assign('relatedEvents', $relatedEvents);
        }
    }

    /**
     * Check, if event belongs to user
     * If event does not belong to user, redirect to list action
     *
     * @param Event $event
     * @throws PropagateResponseException
     */
    protected function checkAccess(Event $event): void
    {
        if ($event->getMdEventmgtFeuser()->getUid() !== $this->feUser['uid']) {
            $this->addFlashMessage(
                LocalizationUtility::translate('controller.access_error', 'md_eventmgt_frontend'),
                '',
                ContextualFeedbackSeverity::ERROR
            );

            $uri = $this->uriBuilder->uriFor('list');
            $response = $this->responseFactory->createResponse()
                ->withHeader('Location', $uri);

            throw new PropagateResponseException($response, 307);
        }
    }

    /**
     * Send emails
     * This will loop through all configured emails settings and send emails accordingly
     *
     * @param array $data
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmails(array $data)
    {
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        if ($this->settings['emailNotification'] == 1) {
            foreach ($this->settings['emailItems'] as $emails) {
                if ($this->actionMethodName == $emails['container']['template']) {
                    $dataArr = [
                        'email' => $emails['container']['email'],
                        'name' => $emails['container']['name'],
                        'body' => $emails['container']['body']
                    ];
                    $dataArr = array_merge($dataArr, $data);

                    $this->emailService->sendEmail(
                        ['email' => $this->settings['emailFrom'], 'name' => $this->settings['emailFromName']],
                        ['email' => $emails['container']['email'], 'name' => $emails['container']['name']],
                        $emails['container']['subject'],
                        $emails['container']['template'],
                        $dataArr,
                        $this->settings,
                        $extbaseFrameworkConfiguration,
                        $this->request
                    );
                }
            }
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

        $pagination = new SlidingWindowPagination(
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

    /**
     * Convert german comma-notation to english point-notation
     */
    protected function convertFloat(): void
    {
        if (
            $this->settings['floatpoint'] == ','
            && isset($this->arguments['event'])
            && isset($this->request->getArguments()['event']['price'])
        ) {
            $this->arguments['event']
                ->getPropertyMappingConfiguration()
                ->forProperty('price')
                ->setTypeConverter($this->floatConverter);
        }
    }
}
