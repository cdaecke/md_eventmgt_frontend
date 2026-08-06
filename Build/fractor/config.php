<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([
        __DIR__ . '/../../Configuration',
        __DIR__ . '/../../Resources',
    ])
    ->withSets([
        // a9f/typo3-fractor ^0.4 does not yet ship an UP_TO_TYPO3_14 set; use the highest available.
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ]);
