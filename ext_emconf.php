<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Frontend for ext:sf_event_mgt',
    'description' => 'Create and edit events for ext:sf_event_mgt in the frontend',
    'category' => 'plugin',
    'author' => 'Christoph Daecke',
    'author_email' => 'typo3@mediadreams.org',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.3.99',
            'sf_event_mgt' => '9.0.0-9.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
