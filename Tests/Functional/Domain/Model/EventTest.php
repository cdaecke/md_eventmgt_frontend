<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Tests\Functional\Domain\Model;

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
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Covers setMdEventmgtFeuserByUid(), which uses GeneralUtility::makeInstance()
 * to fetch a FrontendUserRepository - this needs a real DB/DI container and
 * therefore can't be covered by a unit test (see EventTest in Tests/Unit).
 */
#[CoversClass(Event::class)]
final class EventTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'derhansen/sf_event_mgt',
        'mediadreams/md_eventmgt_frontend',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/FrontendUser.csv');
    }

    #[Test]
    public function setMdEventmgtFeuserByUidSetsMatchingFrontendUser(): void
    {
        $event = new Event();
        $event->setMdEventmgtFeuserByUid(1);

        self::assertInstanceOf(FrontendUser::class, $event->getMdEventmgtFeuser());
        self::assertSame(1, $event->getMdEventmgtFeuser()?->getUid());
    }

    #[Test]
    public function setMdEventmgtFeuserByUidWithUnknownUidLeavesFeuserNull(): void
    {
        $event = new Event();
        $event->setMdEventmgtFeuserByUid(999999);

        self::assertNull($event->getMdEventmgtFeuser());
    }

    #[Test]
    public function setMdEventmgtFeuserByUidWithUnknownUidDoesNotOverwriteExistingFeuser(): void
    {
        $event = new Event();
        $event->setMdEventmgtFeuserByUid(1);
        $event->setMdEventmgtFeuserByUid(999999);

        self::assertSame(1, $event->getMdEventmgtFeuser()?->getUid());
    }
}
