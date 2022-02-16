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

use DERHANSEN\SfEventMgt\Service\EventCacheService;
use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use Mediadreams\MdEventmgtFrontend\Event\CreateActionAfterPersistEvent;
use Mediadreams\MdEventmgtFrontend\Event\CreateActionBeforeSaveEvent;
use Mediadreams\MdEventmgtFrontend\Event\DeleteActionBeforeDeleteEvent;
use Mediadreams\MdEventmgtFrontend\Event\UpdateActionBeforeSaveEvent;
use Mediadreams\MdEventmgtFrontend\Service\SlugService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class EventController
 * @package Mediadreams\MdEventmgtFrontend\Controller
 */
class EventController extends AbstractController
{
    /**
     * slugService
     *
     * @var EventCacheService
     */
    protected $eventCacheService = null;

    /**
     * slugService
     *
     * @var SlugService
     */
    protected $slugService = null;

    /**
     * persistenceManager
     *
     * @var PersistenceManager
     */
    protected $persistenceManager = null;

    /**
     * @param EventCacheService $eventCacheService
     */
    public function injectEventCacheService(EventCacheService $eventCacheService)
    {
        $this->eventCacheService = $eventCacheService;
    }

    /**
     * @param SlugService $slugService
     */
    public function injectSlugService(SlugService $slugService)
    {
        $this->slugService = $slugService;
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * action access
     *
     * This will be called, if user is not logged in
     */
    public function accessAction(): void
    {

    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction(): void
    {
        $this->eventRepository->setDefaultOrderings(['startdate' => QueryInterface::ORDER_DESCENDING]);
        $events = $this->eventRepository->findByMdEventmgtFeuser($this->feUser['uid']);
        $this->assignPagination($events);
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction(): void
    {
        // Allow to pass data via link to form
        // Example: <f:link.action action="new" controller="Event" arguments="{title:'myTitle'}">New myTitle</f:link.action>
        $arguments = $this->request->getArguments();
        $this->view->assign('event', $arguments);
    }

    /**
     * Initialize action create
     */
    public function initializeCreateAction(): void
    {
        $this->convertFloat();
    }

    /**
     * action create
     *
     * @param Event $event
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    public function createAction(Event $event): void
    {
        $event->setMdEventmgtFeuser($this->feUser['uid']);
        $this->setTime($event);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new CreateActionBeforeSaveEvent($event, $this, $this->settings));

        $this->eventRepository->add($event);

        // Persist event in order to get the uid of the entry
        $this->persistenceManager->persistAll();

        // Add slug
        $slug = $this->slugService->getSlug($event);
        $event->setSlug($slug);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new CreateActionAfterPersistEvent($event, $this, $this->settings));

        $this->eventRepository->update($event);

        // Send notification emails
        $this->sendEmails(['event' => $event, 'feUser' => $this->feUser]);

        $this->addFlashMessage(
            LocalizationUtility::translate('controller.created', 'md_eventmgt_frontend'),
            '',
            AbstractMessage::OK
        );

        $this->redirect('list');
    }

    /**
     * action edit
     *
     * @param Event $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     * @return void
     */
    public function editAction(Event $event): void
    {
        $this->checkAccess($event);

        $this->view->assign('event', $event);
    }

    /**
     * Initialize action update
     */
    public function initializeUpdateAction(): void
    {
        $this->convertFloat();
    }

    /**
     * action update
     *
     * @param Event $event
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateAction(Event $event): void
    {
        $this->checkAccess($event);

        $this->setTime($event);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new UpdateActionBeforeSaveEvent($event, $this, $this->settings));

        $this->eventRepository->update($event);

        // Send notification emails
        $this->sendEmails(['event' => $event, 'feUser' => $this->feUser]);

        $this->eventCacheService->flushEventCache($event->getUid(), $event->getPid());

        $this->addFlashMessage(
            LocalizationUtility::translate('controller.updated', 'md_eventmgt_frontend'),
            '',
            AbstractMessage::OK
        );

        $this->redirect('list');
    }

    /**
     * action delete
     *
     * @param Event $event
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteAction(Event $event): void
    {
        $this->checkAccess($event);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new DeleteActionBeforeDeleteEvent($event, $this, $this->settings));

        // Send notification emails
        $this->sendEmails(['event' => $event, 'feUser' => $this->feUser]);

        $this->addFlashMessage(
            LocalizationUtility::translate('controller.deleted', 'md_eventmgt_frontend'),
            '',
            AbstractMessage::OK
        );

        $this->eventRepository->remove($event);
        $this->redirect('list');
    }

}
