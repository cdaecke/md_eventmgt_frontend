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

/**
 * Class BaseEvent
 * @package Mediadreams\MdEventmgtFrontend\Event
 */
abstract class BaseEvent
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var EventController
     */
    private $eventController;

    /**
     * BaseEvent constructor.
     *
     * @param Event $event
     * @param EventController $eventController
     */
    public function __construct(Event $event, EventController $eventController)
    {
        $this->event = $event;
        $this->eventController = $eventController;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return EventController
     */
    public function getEventController(): EventController
    {
        return $this->eventController;
    }
}
