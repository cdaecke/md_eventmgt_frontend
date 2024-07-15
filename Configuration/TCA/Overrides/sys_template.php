<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'md_eventmgt_frontend',
    'Configuration/TypoScript',
    'Frontend for ext:sf_event_mgt'
);
