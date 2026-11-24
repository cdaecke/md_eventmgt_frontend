<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Tests\Functional\Controller;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2026 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Controller\AbstractController;
use Mediadreams\MdEventmgtFrontend\Event\CheckAccessEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Access checks with a registered `CheckAccessEvent` listener, see
 * Fixtures/EventListener/CheckAccessEventListener.php.
 *
 * The default behaviour without any listener is covered by EventControllerTest.
 */
#[CoversClass(AbstractController::class)]
#[CoversClass(CheckAccessEvent::class)]
final class EventControllerCheckAccessEventTest extends FunctionalTestCase
{
    private const UID_OF_PAGE = 1;
    private const UID_OF_EVENT = 1;
    private const UID_OF_OWNER = 1;
    private const UID_OF_ADMIN = 3;

    protected array $testExtensionsToLoad = [
        'derhansen/sf_event_mgt',
        'mediadreams/md_eventmgt_frontend',
        __DIR__ . '/Fixtures/Extensions/check_access_listener',
    ];

    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/md_eventmgt_frontend/Tests/Functional/Controller/Fixtures/Sites/' => 'typo3conf/sites',
    ];

    protected array $configurationToUseInTestInstance = [
        'FE' => [
            'cacheHash' => [
                'enforceValidation' => false,
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/SiteStructure.csv');
        $this->setUpFrontendRootPage(self::UID_OF_PAGE, [
            'constants' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/constants.typoscript',
                'EXT:md_eventmgt_frontend/Configuration/TypoScript/constants.typoscript',
            ],
            'setup' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
                'EXT:md_eventmgt_frontend/Configuration/TypoScript/setup.typoscript',
                'EXT:md_eventmgt_frontend/Tests/Functional/Controller/Fixtures/TypoScript/Setup/Rendering.typoscript',
            ],
        ]);

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/ContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/FrontendUser.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/AdminUser.csv');
    }

    #[Test]
    public function deleteActionWithEventFromOtherUserDeletesEventIfListenerGrantsAccess(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'delete',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_ADMIN, 'POST');

        $this->assertCSVDataSet(
            __DIR__ . '/Assertions/Database/EventController/Delete/SoftDeletedEvent.csv',
        );
    }

    #[Test]
    public function editActionWithEventFromOtherUserRedirectsToListIfListenerGrantsOnlyDelete(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'edit',
            'tx_mdeventmgtfrontend_frontend[event]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_ADMIN);

        self::assertRedirectsToListAction($response);
    }

    #[Test]
    public function editActionWithOwnEventRedirectsToListIfListenerRevokesAccess(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/LockedEventOwnedByUserA.csv');

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'edit',
            'tx_mdeventmgtfrontend_frontend[event]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER);

        self::assertRedirectsToListAction($response);
    }

    #[Test]
    public function deleteActionWithOwnEventDoesNotDeleteEventIfListenerRevokesAccess(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/LockedEventOwnedByUserA.csv');

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'delete',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER, 'POST');

        self::assertRedirectsToListAction($response);
        $this->assertCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/LockedEventOwnedByUserA.csv');
    }

    /**
     * @param array<string, string> $queryParameters
     * @param positive-int $userUid
     */
    private function executeRequestWithLoggedInUser(array $queryParameters, int $userUid, string $method = 'GET'): ResponseInterface
    {
        $request = (new InternalRequest())
            ->withPageId(self::UID_OF_PAGE)
            ->withQueryParameters($queryParameters)
            ->withMethod($method);

        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        return $this->executeFrontendSubRequest($request, $context);
    }

    private static function assertRedirectsToListAction(ResponseInterface $response): void
    {
        self::assertSame(307, $response->getStatusCode());
        self::assertStringContainsString('action%5D=list', $response->getHeaderLine('Location'));
    }
}
