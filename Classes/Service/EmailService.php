<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Service;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Christoph Daecke <typo3@mediadreams.org>
 */

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\TemplatedEmailFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EmailService
 * @package Mediadreams\MdEventmgtFrontend\Service
 */
class EmailService
{
    public function __construct(
        private readonly TemplatedEmailFactory $templatedEmailFactory,
        private readonly MailerInterface $mailer,
    ) {
    }

    /**
     * Send an email
     *
     * @param array $fromArr The sender, eg. ['email' => 'email@domain.com', 'name' => 'Firstname Lastname']
     * @param array $toArr The recipient, eg. ['email' => 'email@domain.com', 'name' => 'Firstname Lastname']
     * @param string $subject The subject
     * @param string $template The template file
     * @param array $data Variables/data to be passed to template
     * @param array $settings Settings of extension
     * @param array $extbaseFrameworkConfiguration Extbase framework configuration
     * @param ServerRequestInterface $request The current request
     * @return bool
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail(
        array $fromArr,
        array $toArr,
        string $subject,
        string $template,
        array $data,
        array $settings,
        array $extbaseFrameworkConfiguration,
        ServerRequestInterface $request
    ): bool {
        $from = $fromArr['email'];
        $to = $toArr['email'];

        if (!GeneralUtility::validEmail($from) || !GeneralUtility::validEmail($to)) {
            return false;
        }

        if (!empty($fromArr['name'])) {
            $from = new Address($fromArr['email'], $fromArr['name']);
        }

        if (!empty($toArr['name'])) {
            $to = new Address($toArr['email'], $toArr['name']);
        }

        // Templates live in a dedicated "Email" subfolder of the Extbase template root path
        $email = $this->templatedEmailFactory->createWithOverrides(
            templateRootPaths: [$this->getViewPath($extbaseFrameworkConfiguration, 'templateRootPaths') . 'Email/'],
            layoutRootPaths: [$this->getViewPath($extbaseFrameworkConfiguration, 'layoutRootPaths')],
            partialRootPaths: [$this->getViewPath($extbaseFrameworkConfiguration, 'partialRootPaths')],
            request: $request,
        );

        // Only HTML templates exist for this extension - requesting the default "html + plain"
        // format would make Fluid look for a non-existent plain-text template variant.
        $email
            ->format(FluidEmail::FORMAT_HTML)
            ->setTemplate(ucfirst($template))
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->assign('settings', $settings)
            ->assignMultiple($data);

        $this->mailer->send($email);

        return true;
    }

    /**
     * Resolve the last configured Extbase view path (templateRootPaths/layoutRootPaths/partialRootPaths)
     */
    private function getViewPath(array $extbaseFrameworkConfiguration, string $type): string
    {
        return GeneralUtility::getFileAbsFileName(end($extbaseFrameworkConfiguration['view'][$type]));
    }
}
