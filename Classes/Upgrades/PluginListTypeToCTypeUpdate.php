<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Upgrades;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('mdeventmgtfrontendExtensionPluginListTypeToCTypeUpdate')]
final class PluginListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'mdeventmgtfrontend_frontend' => 'mdeventmgtfrontend_frontend',
        ];
    }

    public function getTitle(): string
    {
        return 'EXT:md_eventmgt_frontend: Migrate plugins';
    }

    public function getDescription(): string
    {
        return 'Migrates mdeventmgtfrontend_frontend from list_type to CType.';
    }
}
