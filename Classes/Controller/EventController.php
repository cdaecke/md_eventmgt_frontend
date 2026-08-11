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
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Html\SanitizerBuilderFactory;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Attribute\IgnoreValidation;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class EventController
 */
class EventController extends AbstractController
{
    public function __construct(protected EventCacheService $eventCacheService, protected PersistenceManager $persistenceManager, protected SlugService $slugService) {}
    /**
     * This will be called, if user is not logged in
     */
    public function accessAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * action list
     *
     * @return ResponseInterface
     */
    public function listAction(): ResponseInterface
    {
        $this->eventRepository->setDefaultOrderings(['startdate' => QueryInterface::ORDER_DESCENDING]);
        $events = $this->eventRepository->findBy(['mdEventmgtFeuser' => $this->feUser['uid']]);
        $this->assignPagination($events);

        return $this->htmlResponse();
    }

    /**
     * action new
     *
     * @return ResponseInterface
     */
    public function newAction(): ResponseInterface
    {
        // Allow to pass data via link to form
        // Example: <f:link.action action="new" controller="Event" arguments="{title:'myTitle'}">New myTitle</f:link.action>
        $arguments = $this->request->getArguments();
        $this->view->assign('event', $arguments);

        return $this->htmlResponse();
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
     * @return ResponseInterface
     */
    public function createAction(Event $event): ResponseInterface
    {
        $event->setMdEventmgtFeuserByUid($this->feUser['uid']);
        $this->setTime($event);
        $this->sanitizeUserProvidedHtml($event);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new CreateActionBeforeSaveEvent($event, $this, $this->settings, $this->request));

        $this->eventRepository->add($event);

        // Persist event in order to get the uid of the entry
        $this->persistenceManager->persistAll();

        // Add slug
        $slug = $this->slugService->getSlug($event);
        $event->setSlug($slug);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new CreateActionAfterPersistEvent($event, $this, $this->settings, $this->request));

        $this->eventRepository->update($event);

        // Send notification emails
        $this->sendEmails(['event' => $event, 'feUser' => $this->feUser]);

        $this->eventCacheService->flushEventCache((int)$event->getUid(), (int)$event->getPid());

        $this->addFlashMessage(
            LocalizationUtility::translate('controller.created', 'MdEventmgtFrontend') ?? '',
            '',
            ContextualFeedbackSeverity::OK
        );

        return $this->redirect('list');
    }

    /**
     * action edit
     *
     * @param Event $event
     * @return ResponseInterface
     */
    public function editAction(#[IgnoreValidation] Event $event): ResponseInterface
    {
        $this->checkAccess($event);

        $this->view->assign('event', $event);

        return $this->htmlResponse();
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
     * @return ResponseInterface
     */
    public function updateAction(Event $event): ResponseInterface
    {
        $this->checkAccess($event);

        $this->setTime($event);
        $this->sanitizeUserProvidedHtml($event);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new UpdateActionBeforeSaveEvent($event, $this, $this->settings, $this->request));

        $this->eventRepository->update($event);

        // Send notification emails
        $this->sendEmails(['event' => $event, 'feUser' => $this->feUser]);

        $this->eventCacheService->flushEventCache((int)$event->getUid(), (int)$event->getPid());

        $this->addFlashMessage(
            LocalizationUtility::translate('controller.updated', 'MdEventmgtFrontend') ?? '',
            '',
            ContextualFeedbackSeverity::OK
        );

        return $this->redirect('list');
    }

    /**
     * Reject deletion requests that were not sent as POST.
     *
     * A GET-triggerable delete allows the action to be invoked by a mere link or an <img> tag -
     * something an attacker can embed anywhere a logged-in user's browser will load it, using
     * that user's own session to delete their own data without their intent (CSRF). Requiring
     * POST closes that off: browsers can only ever send POST via an actual form submission, and
     * SameSite=Lax (TYPO3's default fe_typo_user cookie policy) withholds the session cookie from
     * cross-site POST submissions.
     *
     * @throws \TYPO3\CMS\Core\Http\PropagateResponseException
     */
    public function initializeDeleteAction(): void
    {
        if ($this->request->getMethod() !== 'POST') {
            $this->addFlashMessage(
                LocalizationUtility::translate('controller.invalid_request', 'MdEventmgtFrontend') ?? '',
                '',
                ContextualFeedbackSeverity::ERROR
            );

            $uri = $this->uriBuilder->uriFor('list');
            $response = $this->responseFactory->createResponse(307)
                ->withHeader('Location', $uri);

            throw new PropagateResponseException($response, 307);
        }
    }

    /**
     * action delete
     *
     * @param Event $event
     * @return ResponseInterface
     */
    public function deleteAction(Event $event): ResponseInterface
    {
        $this->checkAccess($event);

        // PSR-14 Event
        $this->eventDispatcher->dispatch(new DeleteActionBeforeDeleteEvent($event, $this, $this->settings, $this->request));

        // Send notification emails
        $this->sendEmails(['event' => $event, 'feUser' => $this->feUser]);

        $this->addFlashMessage(
            LocalizationUtility::translate('controller.deleted', 'MdEventmgtFrontend') ?? '',
            '',
            ContextualFeedbackSeverity::OK
        );

        $this->eventRepository->remove($event);

        $this->eventCacheService->flushEventCache((int)$event->getUid(), (int)$event->getPid());

        return $this->redirect('list');
    }

    /**
     * Sanitize the free-text fields the frontend form offers for editing (teaser/description/program).
     * Extbase persistence bypasses the DataHandler/RTE transformation pipeline entirely, so nothing
     * else strips dangerous markup before this is stored - and sf_event_mgt's own Detail template
     * renders description/program via f:format.html (escaping disabled), so this needs to happen on
     * write, not on read: we don't control sf_event_mgt's templates or any other consumer of this data.
     */
    private function sanitizeUserProvidedHtml(Event $event): void
    {
        $sanitizer = GeneralUtility::makeInstance(SanitizerBuilderFactory::class)->build('default')->build();

        $event->setTeaser($sanitizer->sanitize($event->getTeaser()));
        $event->setDescription($sanitizer->sanitize($event->getDescription()));
        $event->setProgram($sanitizer->sanitize($event->getProgram()));
    }
}
