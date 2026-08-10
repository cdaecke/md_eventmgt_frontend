<?php

declare(strict_types=1);

use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create();
$config->getFinder()
    ->in(__DIR__ . '/../../Classes')
    ->in(__DIR__ . '/../../Configuration')
    ->in(__DIR__ . '/../../Tests');

return $config;
