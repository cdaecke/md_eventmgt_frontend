<?php

declare(strict_types=1);

return [
    \Mediadreams\MdEventmgtFrontend\Domain\Model\Category::class => [
        'tableName' => 'sys_category',
    ],

    \Mediadreams\MdEventmgtFrontend\Domain\Model\Event::class => [
        'tableName' => 'tx_sfeventmgt_domain_model_event',
    ],

    \Mediadreams\MdEventmgtFrontend\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],

    \Mediadreams\MdEventmgtFrontend\Domain\Model\FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
    ],
];
