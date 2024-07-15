<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MdEventmgtFrontend',
    'Frontend',
    'Event management frontend'
);

// add flexform
$pluginSignature = 'mdeventmgtfrontend_frontend';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:md_eventmgt_frontend/Configuration/FlexForms/PluginFrontend.xml'
);
