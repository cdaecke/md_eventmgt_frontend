<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Tests\Unit\Service;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2026 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\Service\EmailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\TemplatedEmailFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Regression test for a real bug: EmailService used to pick the view path via
 * end($paths), which silently picked up an empty string left by an unset
 * TypoScript constant / Site Setting default instead of the real configured
 * path. Fixed by filtering out empty entries instead of blindly taking the
 * last one.
 */
#[CoversClass(EmailService::class)]
final class EmailServiceTest extends UnitTestCase
{
    #[Test]
    public function sendEmailFiltersEmptyViewPathEntriesBeforeCreatingEmail(): void
    {
        $publicPath = Environment::getPublicPath();
        $templatePath = $publicPath . '/typo3conf/ext/md_eventmgt_frontend/Resources/Private/Templates/';
        $layoutPath = $publicPath . '/typo3conf/ext/md_eventmgt_frontend/Resources/Private/Layouts/';
        $partialPath = $publicPath . '/typo3conf/ext/md_eventmgt_frontend/Resources/Private/Partials/';

        // Mirrors the real bug scenario: index 0 is the extension's own EXT: path,
        // index 1 is an unset TypoScript constant / Site Setting default -> empty string.
        $extbaseFrameworkConfiguration = [
            'view' => [
                'templateRootPaths' => [$templatePath, ''],
                'layoutRootPaths' => [$layoutPath, ''],
                'partialRootPaths' => [$partialPath, ''],
            ],
        ];

        $fluidEmail = $this->createMock(FluidEmail::class);
        $fluidEmail->method('format')->willReturnSelf();
        $fluidEmail->method('setTemplate')->willReturnSelf();
        $fluidEmail->method('from')->willReturnSelf();
        $fluidEmail->method('to')->willReturnSelf();
        $fluidEmail->method('subject')->willReturnSelf();
        $fluidEmail->method('assign')->willReturnSelf();
        $fluidEmail->method('assignMultiple')->willReturnSelf();

        $templatedEmailFactory = $this->createMock(TemplatedEmailFactory::class);
        $templatedEmailFactory->expects(self::once())
            ->method('createWithOverrides')
            ->with(
                [$templatePath . 'Email/'],
                [$layoutPath],
                [$partialPath],
                self::isInstanceOf(ServerRequestInterface::class),
            )
            ->willReturn($fluidEmail);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $subject = new EmailService($templatedEmailFactory, $mailer);

        $result = $subject->sendEmail(
            ['email' => 'sender@example.com', 'name' => 'Sender'],
            ['email' => 'recipient@example.com', 'name' => 'Recipient'],
            'Test subject',
            'createAction',
            [],
            [],
            $extbaseFrameworkConfiguration,
            $this->createMock(ServerRequestInterface::class),
        );

        self::assertTrue($result);
    }

    #[Test]
    public function sendEmailReturnsFalseForInvalidFromAddress(): void
    {
        $subject = new EmailService(
            $this->createMock(TemplatedEmailFactory::class),
            $this->createMock(MailerInterface::class),
        );

        $result = $subject->sendEmail(
            ['email' => 'not-an-email', 'name' => 'Sender'],
            ['email' => 'recipient@example.com', 'name' => 'Recipient'],
            'Test subject',
            'createAction',
            [],
            [],
            ['view' => ['templateRootPaths' => [], 'layoutRootPaths' => [], 'partialRootPaths' => []]],
            $this->createMock(ServerRequestInterface::class),
        );

        self::assertFalse($result);
    }
}
