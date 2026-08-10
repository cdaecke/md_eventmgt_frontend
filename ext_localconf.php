<?php

defined('TYPO3') or die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'MdEventmgtFrontend',
        'Frontend',
        [
            \Mediadreams\MdEventmgtFrontend\Controller\EventController::class => 'list, access, new, create, edit, update, delete'
        ],
        // non-cacheable actions
        [
            \Mediadreams\MdEventmgtFrontend\Controller\EventController::class => 'list, new, edit, create, update, delete'
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
    );

    /**
     * Extend ext:sf_event_mgt model
     */
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\DERHANSEN\SfEventMgt\Domain\Model\Event::class] = [
        'className' => \Mediadreams\MdEventmgtFrontend\Domain\Model\Event::class
    ];
})();
