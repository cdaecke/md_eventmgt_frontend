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

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class EmailService
 * @package Mediadreams\MdEventmgtFrontend\Service
 */
class EmailService
{
    /**
     * standaloneView
     *
     * @var StandaloneView
     */
    protected $standaloneView = null;

    /**
     * @param StandaloneView $standaloneView
     */
    public function injectStandaloneView(StandaloneView $standaloneView)
    {
        $this->standaloneView = $standaloneView;
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
     * @param object $controllerContext ControllerContext
     * @param array $extbaseFrameworkConfiguration Extbase framework configuration
     * @return bool
     */
    public function sendEmail(
        array $fromArr,
        array $toArr,
        string $subject,
        string $template,
        array $data,
        array $settings,
        $controllerContext,
        array $extbaseFrameworkConfiguration
    ) {
        $from = $fromArr['email'];
        $to = $toArr['email'];

        if (!GeneralUtility::validEmail($from) || !GeneralUtility::validEmail($to)) {
            return false;
        }

        // Initialize view for email template
        $this->initializeView($controllerContext, $extbaseFrameworkConfiguration, $template);
        $this->standaloneView->assign('settings', $settings);
        $this->standaloneView->assignMultiple($data);

        if (!empty($fromArr['name'])) {
            $from = new Address($fromArr['email'], $fromArr['name']);
        }

        if (!empty($toArr['name'])) {
            $to = new Address($toArr['email'], $toArr['name']);
        }

        // Send email
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($this->standaloneView->render())
            ->send();

        return $mail->isSent();
    }

    /**
     * Initialize view for email templates
     *
     * @param object $controllerContext ControllerContext
     * @param array $extbaseFrameworkConfiguration Extbase framework configuration
     * @param string $templateName The template name
     */
    protected function initializeView($controllerContext, $extbaseFrameworkConfiguration, string $templateName): void
    {
        // Needed for translations in fluid template
        $this->standaloneView->setControllerContext($controllerContext);

        // Templates path
        $templateRootPath = GeneralUtility::getFileAbsFileName(end($extbaseFrameworkConfiguration['view']['templateRootPaths']));
        $templatePathAndFilename = $templateRootPath . 'Email/' . ucfirst($templateName) . '.html';

        // Layouts path
        $layoutRootPath = GeneralUtility::getFileAbsFileName(end($extbaseFrameworkConfiguration['view']['layoutRootPaths']));
        $this->standaloneView->setLayoutRootPaths(array($layoutRootPath));

        // Partials path
        $partialRootPath = GeneralUtility::getFileAbsFileName(end($extbaseFrameworkConfiguration['view']['partialRootPaths']));
        $this->standaloneView->setPartialRootPaths(array($partialRootPath));

        $this->standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
    }
}
