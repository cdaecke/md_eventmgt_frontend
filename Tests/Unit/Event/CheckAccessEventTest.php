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

use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use Mediadreams\MdEventmgtFrontend\Event\CheckAccessEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CheckAccessEvent::class)]
final class CheckAccessEventTest extends UnitTestCase
{
    private Event $event;
    private RequestInterface $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new Event();
        $this->request = $this->createMock(RequestInterface::class);
    }

    #[Test]
    public function gettersReturnValuesPassedToConstructor(): void
    {
        $feUser = ['uid' => 1, 'username' => 'user-a'];
        $settings = ['emailNotification' => 1];

        $subject = new CheckAccessEvent($this->event, $feUser, 'delete', $settings, $this->request, true);

        self::assertSame($this->event, $subject->getEvent());
        self::assertSame($feUser, $subject->getFeUser());
        self::assertSame('delete', $subject->getActionName());
        self::assertSame($settings, $subject->getSettings());
        self::assertSame($this->request, $subject->getRequest());
        self::assertTrue($subject->isAccessGranted());
    }

    #[Test]
    public function setAccessGrantedGrantsAccess(): void
    {
        $subject = new CheckAccessEvent($this->event, [], 'edit', [], $this->request, false);

        $subject->setAccessGranted(true);

        self::assertTrue($subject->isAccessGranted());
    }

    #[Test]
    public function setAccessGrantedRevokesAccess(): void
    {
        $subject = new CheckAccessEvent($this->event, [], 'edit', [], $this->request, true);

        $subject->setAccessGranted(false);

        self::assertFalse($subject->isAccessGranted());
    }
}
