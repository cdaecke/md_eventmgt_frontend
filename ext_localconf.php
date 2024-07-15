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
            \Mediadreams\MdEventmgtFrontend\Controller\EventController::class => 'list, create, update, delete'
        ]
    );

    /**
     * Extend ext:sf_event_mgt model
     */
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\DERHANSEN\SfEventMgt\Domain\Model\Event::class] = [
        'className' => \Mediadreams\MdEventmgtFrontend\Domain\Model\Event::class
    ];
})();
