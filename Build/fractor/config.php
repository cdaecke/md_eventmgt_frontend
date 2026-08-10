<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([
        __DIR__ . '/../../Configuration',
        __DIR__ . '/../../Resources',
    ])
    ->withSkip([
        // The XML pretty-printer used by a9f/fractor-xml has a bug: indentation drifts one
        // level deeper after every XML comment (<!-- ... -->) in the file, cascading further
        // with each additional comment, corrupting the file's structure. This FlexForm has
        // three comments and gets its indentation progressively broken. Verified by applying
        // the suggested change and inspecting the actual diff - not a real improvement.
        __DIR__ . '/../../Configuration/FlexForms/PluginFrontend.xml',
    ])
    ->withSets([
        // a9f/typo3-fractor ^0.4 does not yet ship an UP_TO_TYPO3_14 set; use the highest available.
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ]);
