<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Tests\Unit\Domain\Model;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2026 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Domain\Model\Event;
use Mediadreams\MdEventmgtFrontend\Domain\Model\FrontendUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Only covers the properties/accessors this extension adds on top of
 * \DERHANSEN\SfEventMgt\Domain\Model\Event. setMdEventmgtFeuserByUid() is
 * intentionally not covered here - it uses GeneralUtility::makeInstance()
 * to fetch a FrontendUserRepository, which needs a working DB/container and
 * belongs in a functional test instead.
 */
#[CoversClass(Event::class)]
final class EventTest extends UnitTestCase
{
    private Event $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Event();
    }

    #[Test]
    public function getMdEventmgtFeuserInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getMdEventmgtFeuser());
    }

    #[Test]
    public function setMdEventmgtFeuserSetsMdEventmgtFeuser(): void
    {
        $feUser = new FrontendUser();
        $this->subject->setMdEventmgtFeuser($feUser);

        self::assertSame($feUser, $this->subject->getMdEventmgtFeuser());
    }

    #[Test]
    public function setMdEventmgtFeuserAcceptsNull(): void
    {
        $this->subject->setMdEventmgtFeuser(new FrontendUser());
        $this->subject->setMdEventmgtFeuser(null);

        self::assertNull($this->subject->getMdEventmgtFeuser());
    }

    #[Test]
    public function setSlugSetsSlug(): void
    {
        $value = 'my-event-slug';
        $this->subject->setSlug($value);

        self::assertSame($value, $this->subject->getSlug());
    }
}
