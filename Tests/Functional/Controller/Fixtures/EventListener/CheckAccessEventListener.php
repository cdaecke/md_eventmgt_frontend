<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Tests\Functional\Controller\Fixtures\EventListener;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2026 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Event\CheckAccessEvent;

/**
 * Test fixture listener, registered by the fixture extension `check_access_listener`.
 *
 * - Grants access to the `delete` action for the user `admin`, but only if the
 *   frontend TypoScript is reachable via the request (as real listeners need it).
 * - Revokes access to the event titled `Locked event` for everybody, even the owner.
 */
final class CheckAccessEventListener
{
    public const ADMIN_USERNAME = 'admin';
    public const LOCKED_EVENT_TITLE = 'Locked event';

    public function __invoke(CheckAccessEvent $checkAccessEvent): void
    {
        if (
            $checkAccessEvent->getActionName() === 'delete'
            && ($checkAccessEvent->getFeUser()['username'] ?? '') === self::ADMIN_USERNAME
            && $checkAccessEvent->getRequest()->getAttribute('frontend.typoscript') !== null
        ) {
            $checkAccessEvent->setAccessGranted(true);
        }

        if ($checkAccessEvent->getEvent()->getTitle() === self::LOCKED_EVENT_TITLE) {
            $checkAccessEvent->setAccessGranted(false);
        }
    }
}
