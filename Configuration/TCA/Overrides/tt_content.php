<?php

defined('TYPO3') or die();

$frontendPluginSignature = \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MdEventmgtFrontend',
    'Frontend',
    'Event management frontend',
    null,
    null,
    'Plugin for manage entries of ext:sf_event_mgt in the frontend.',
    'FILE:EXT:md_eventmgt_frontend/Configuration/FlexForms/PluginFrontend.xml',
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,pi_flexform,pages,recursive',
    $frontendPluginSignature,
    'after:palette:headers',
);
