<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Event;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Controller\EventController;
use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Class BaseEvent
 */
abstract class BaseEvent
{
    /**
     * BaseEvent constructor.
     *
     * @param Event $event
     * @param EventController $eventController
     * @param array $settings
     */
    public function __construct(private Event $event, private readonly EventController $eventController, private array $settings, private readonly RequestInterface $request) {}

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getEventController(): EventController
    {
        return $this->eventController;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
