<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Frontend for ext:sf_event_mgt',
    'description' => 'Create and edit events for ext:sf_event_mgt in the frontend',
    'category' => 'plugin',
    'author' => 'Christoph Daecke',
    'author_email' => 'typo3@mediadreams.org',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.1-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'sf_event_mgt' => '5.0.0-5.99.99',
            'numbered_pagination' => '1.0-1.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
