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

use Mediadreams\MdEventmgtFrontend\Controller\EventController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(EventController::class)]
final class EventControllerTest extends FunctionalTestCase
{
    private const UID_OF_PAGE = 1;
    private const UID_OF_EVENT = 1;
    private const UID_OF_OWNER = 1;
    private const UID_OF_OTHER_USER = 2;

    protected array $testExtensionsToLoad = [
        'derhansen/sf_event_mgt',
        'mediadreams/md_eventmgt_frontend',
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
    }

    #[Test]
    public function editActionWithOwnEventAssignsProvidedEventToView(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        $html = $this->getHtmlWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'edit',
            'tx_mdeventmgtfrontend_frontend[event]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER);

        self::assertStringContainsString(
            '<input type="hidden" name="tx_mdeventmgtfrontend_frontend[event][__identity]" value="1"',
            $html,
        );
        self::assertStringContainsString('Event by User A', $html);
    }

    #[Test]
    public function editActionWithEventFromOtherUserRedirectsToListWithAccessError(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserB.csv');

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'edit',
            'tx_mdeventmgtfrontend_frontend[event]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER);

        self::assertRedirectsToListAction($response);
    }

    #[Test]
    public function editActionWithEventWithoutOwnerRedirectsToListInsteadOfFatalError(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventWithoutOwner.csv');

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'edit',
            'tx_mdeventmgtfrontend_frontend[event]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER);

        self::assertRedirectsToListAction($response);
    }

    #[Test]
    public function updateActionWithOwnEventPersistsNewTitle(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[__trustedProperties]' => $this->getTrustedPropertiesFromEditForm(
                self::UID_OF_EVENT,
            ),
            'tx_mdeventmgtfrontend_frontend[action]' => 'update',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
            'tx_mdeventmgtfrontend_frontend[event][title]' => 'Updated by owner',
        ], self::UID_OF_OWNER);

        $this->assertCSVDataSet(
            __DIR__ . '/Assertions/Database/EventController/Update/UpdatedEventWithTitle.csv',
        );
    }

    #[Test]
    public function updateActionSanitizesDangerousHtmlInTeaserDescriptionAndProgram(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[__trustedProperties]' => $this->getTrustedPropertiesFromEditForm(
                self::UID_OF_EVENT,
            ),
            'tx_mdeventmgtfrontend_frontend[action]' => 'update',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
            'tx_mdeventmgtfrontend_frontend[event][teaser]' => 'Safe teaser <script>alert(1)</script> text',
            'tx_mdeventmgtfrontend_frontend[event][description]' => 'Safe description <img src=x onerror="alert(1)"> text',
            'tx_mdeventmgtfrontend_frontend[event][program]' => 'Safe program <script>alert(1)</script> text',
        ], self::UID_OF_OWNER);

        $row = $this->getConnectionPool()
            ->getConnectionForTable('tx_sfeventmgt_domain_model_event')
            ->select(['teaser', 'description', 'program'], 'tx_sfeventmgt_domain_model_event', ['uid' => self::UID_OF_EVENT])
            ->fetchAssociative();

        foreach (['teaser', 'description', 'program'] as $field) {
            self::assertStringNotContainsStringIgnoringCase('<script', $row[$field], "Field \"$field\" still contains a <script> tag");
            self::assertStringNotContainsStringIgnoringCase('onerror', $row[$field], "Field \"$field\" still contains an onerror handler");
            self::assertStringContainsString('Safe', $row[$field], "Field \"$field\" lost its safe content");
            self::assertStringContainsString('text', $row[$field], "Field \"$field\" lost its safe content");
        }
    }

    #[Test]
    public function updateActionWithEventFromOtherUserRedirectsToListAndDoesNotPersistChange(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        // Fetch a valid trustedProperties token as the legitimate owner - the token itself is
        // not tied to a specific record, only to the allowed property names, so it can be
        // replayed by the attacking user below. This mirrors a real forged-request attack.
        $trustedProperties = $this->getTrustedPropertiesFromEditForm(self::UID_OF_EVENT);

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[__trustedProperties]' => $trustedProperties,
            'tx_mdeventmgtfrontend_frontend[action]' => 'update',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
            'tx_mdeventmgtfrontend_frontend[event][title]' => 'Hijacked by other user',
        ], self::UID_OF_OTHER_USER);

        self::assertRedirectsToListAction($response);
        $this->assertCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');
    }

    #[Test]
    public function deleteActionWithOwnEventRemovesProvidedEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'delete',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER, 'POST');

        $this->assertCSVDataSet(
            __DIR__ . '/Assertions/Database/EventController/Delete/SoftDeletedEvent.csv',
        );
    }

    #[Test]
    public function deleteActionWithEventFromOtherUserRedirectsToListAndDoesNotDeleteEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'delete',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OTHER_USER, 'POST');

        self::assertRedirectsToListAction($response);
        $this->assertCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');
    }

    #[Test]
    public function deleteActionWithEventWithoutOwnerRedirectsToListInsteadOfFatalError(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventWithoutOwner.csv');

        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'delete',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER, 'POST');

        self::assertRedirectsToListAction($response);
    }

    #[Test]
    public function deleteActionViaGetIsRejectedAndDoesNotDeleteEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');

        // A GET-triggerable delete could be invoked via a mere <img>/<a> tag, using the victim's
        // own session without their intent (CSRF). deleteAction must only accept POST.
        $response = $this->executeRequestWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'delete',
            'tx_mdeventmgtfrontend_frontend[event][__identity]' => (string)self::UID_OF_EVENT,
        ], self::UID_OF_OWNER, 'GET');

        self::assertRedirectsToListAction($response);
        $this->assertCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/EventOwnedByUserA.csv');
    }

    #[Test]
    public function listActionDoesNotContainEventOwnedByOtherUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/EventController/TwoEventsOwnedByDifferentUsers.csv');

        $html = $this->getHtmlWithLoggedInUser([], self::UID_OF_OWNER);

        self::assertStringContainsString('Event by User A', $html);
        self::assertStringNotContainsString('Event by User B', $html);
    }

    /**
     * @param array<string, string> $queryParameters
     */
    private function getHtmlWithLoggedInUser(array $queryParameters, int $userUid): string
    {
        return (string)$this->executeRequestWithLoggedInUser($queryParameters, $userUid)->getBody();
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

    private function getTrustedPropertiesFromEditForm(int $eventUid): string
    {
        $html = $this->getHtmlWithLoggedInUser([
            'tx_mdeventmgtfrontend_frontend[action]' => 'edit',
            'tx_mdeventmgtfrontend_frontend[event]' => (string)$eventUid,
        ], self::UID_OF_OWNER);

        $matches = [];
        preg_match('/__trustedProperties]" value="([a-zA-Z0-9&{};:,_\[\]]+)"/', $html, $matches);
        if (!isset($matches[1])) {
            throw new \RuntimeException('Could not fetch trustedProperties from returned HTML.', 1754470000);
        }

        return html_entity_decode($matches[1]);
    }

    private static function assertRedirectsToListAction(ResponseInterface $response): void
    {
        self::assertSame(307, $response->getStatusCode());
        self::assertStringContainsString('action%5D=list', $response->getHeaderLine('Location'));
    }
}
