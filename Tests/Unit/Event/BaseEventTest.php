<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Tests\Unit\Event;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2026 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Controller\EventController;
use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use Mediadreams\MdEventmgtFrontend\Event\BaseEvent;
use Mediadreams\MdEventmgtFrontend\Event\CreateActionAfterPersistEvent;
use Mediadreams\MdEventmgtFrontend\Event\CreateActionBeforeSaveEvent;
use Mediadreams\MdEventmgtFrontend\Event\DeleteActionBeforeDeleteEvent;
use Mediadreams\MdEventmgtFrontend\Event\UpdateActionBeforeSaveEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * BaseEvent's behaviour is exercised via CreateActionBeforeSaveEvent as a
 * representative subclass - the other three concrete PSR-14 event classes
 * are empty subclasses distinguished only by their class identity (used for
 * EventListener dispatch), so a separate test class per subclass would only
 * duplicate this coverage.
 */
#[CoversClass(BaseEvent::class)]
#[CoversClass(CreateActionBeforeSaveEvent::class)]
final class BaseEventTest extends UnitTestCase
{
    private Event $event;
    private EventController $eventController;
    private array $settings;
    private RequestInterface $request;
    private CreateActionBeforeSaveEvent $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new Event();
        $this->eventController = $this->createMock(EventController::class);
        $this->settings = ['emailNotification' => 1];
        $this->request = $this->createMock(RequestInterface::class);

        $this->subject = new CreateActionBeforeSaveEvent(
            $this->event,
            $this->eventController,
            $this->settings,
            $this->request,
        );
    }

    #[Test]
    public function getEventReturnsEventPassedToConstructor(): void
    {
        self::assertSame($this->event, $this->subject->getEvent());
    }

    #[Test]
    public function setEventOverridesEvent(): void
    {
        $otherEvent = new Event();
        $this->subject->setEvent($otherEvent);

        self::assertSame($otherEvent, $this->subject->getEvent());
    }

    #[Test]
    public function getEventControllerReturnsEventControllerPassedToConstructor(): void
    {
        self::assertSame($this->eventController, $this->subject->getEventController());
    }

    #[Test]
    public function getSettingsReturnsSettingsPassedToConstructor(): void
    {
        self::assertSame($this->settings, $this->subject->getSettings());
    }

    #[Test]
    public function setSettingsOverridesSettings(): void
    {
        $otherSettings = ['emailNotification' => 0];
        $this->subject->setSettings($otherSettings);

        self::assertSame($otherSettings, $this->subject->getSettings());
    }

    #[Test]
    public function getRequestReturnsRequestPassedToConstructor(): void
    {
        self::assertSame($this->request, $this->subject->getRequest());
    }

    #[Test]
    public function createActionAfterPersistEventExtendsBaseEvent(): void
    {
        self::assertInstanceOf(
            BaseEvent::class,
            new CreateActionAfterPersistEvent($this->event, $this->eventController, $this->settings, $this->request),
        );
    }

    #[Test]
    public function updateActionBeforeSaveEventExtendsBaseEvent(): void
    {
        self::assertInstanceOf(
            BaseEvent::class,
            new UpdateActionBeforeSaveEvent($this->event, $this->eventController, $this->settings, $this->request),
        );
    }

    #[Test]
    public function deleteActionBeforeDeleteEventExtendsBaseEvent(): void
    {
        self::assertInstanceOf(
            BaseEvent::class,
            new DeleteActionBeforeDeleteEvent($this->event, $this->eventController, $this->settings, $this->request),
        );
    }
}
