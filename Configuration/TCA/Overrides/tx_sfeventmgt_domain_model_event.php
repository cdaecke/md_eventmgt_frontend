<?php
defined('TYPO3_MODE') || die();

$tmp_md_eventmgt_frontend_columns = [

    'md_eventmgt_feuser' => [
        'exclude' => true,
        'label' => 'LLL:EXT:md_eventmgt_frontend/Resources/Private/Language/locallang.xlf:tca.md_eventmgt_feuser',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'fe_users',
            'maxitems' => 1,
            'minitems' => 0,
            'size' => 1,
            'default' => 0,
            'suggestOptions' => [
                'default' => [
                    'additionalSearchFields' => 'name, first_name, last_name',
                ]
            ]
        ],
    ],

];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_sfeventmgt_domain_model_event',
    $tmp_md_eventmgt_frontend_columns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_sfeventmgt_domain_model_event',
    '--div--;LLL:EXT:md_eventmgt_frontend/Resources/Private/Language/locallang.xlf:tca.tab,
        md_eventmgt_feuser,
    '
);
