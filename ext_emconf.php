<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Frontend for ext:sf_event_mgt',
    'description' => 'Create and edit events for ext:sf_event_mgt in the frontend',
    'category' => 'plugin',
    'author' => 'Christoph Daecke',
    'author_email' => 'typo3@mediadreams.org',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'sf_event_mgt' => '7.0.0-7.99.99',
            'numbered_pagination' => '1.0.2-2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
