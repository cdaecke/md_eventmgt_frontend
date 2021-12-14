<?php
defined('TYPO3_MODE') || die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'MdEventmgtFrontend',
        'Frontend',
        [
            \Mediadreams\MdEventmgtFrontend\Controller\EventController::class => 'list, access, new, create, edit, update, delete'
        ],
        // non-cacheable actions
        [
            \Mediadreams\MdEventmgtFrontend\Controller\EventController::class => 'list, create, update, delete'
        ]
    );

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'md_eventmgt_frontend-plugin-frontend',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:md_eventmgt_frontend/Resources/Public/Icons/user_plugin_frontend.svg']
    );
})();
