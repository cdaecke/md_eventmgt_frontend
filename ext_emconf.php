<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Frontend for ext:sf_event_mgt',
    'description' => 'Create and edit events for ext:sf_event_mgt in the frontend',
    'category' => 'plugin',
    'author' => 'Christoph Daecke',
    'author_email' => 'typo3@mediadreams.org',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'sf_event_mgt' => '8.0.0-8.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
