<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Event;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2026 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Class CheckAccessEvent
 * This event is triggered, when it is checked, if the current frontend user is allowed to
 * edit, update or delete an event. By default only the owner of the event has access.
 * Listeners can grant (or revoke) access by calling `setAccessGranted()`.
 * If several listeners are registered, the last call of `setAccessGranted()` wins.
 *
 * @package Mediadreams\MdEventmgtFrontend\Event
 */
final class CheckAccessEvent
{
    public function __construct(
        private readonly Event $event,
        private readonly array $feUser,
        private readonly string $actionName,
        private readonly array $settings,
        private readonly RequestInterface $request,
        private bool $accessGranted
    ) {}

    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * Data of logged in frontend user (record of table `fe_users`)
     */
    public function getFeUser(): array
    {
        return $this->feUser;
    }

    /**
     * Name of controller action without suffix, e.g. `edit`, `update` or `delete`
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function isAccessGranted(): bool
    {
        return $this->accessGranted;
    }

    public function setAccessGranted(bool $accessGranted): void
    {
        $this->accessGranted = $accessGranted;
    }
}
