<?php

# autoload the custom fixer
require_once __DIR__ . '/../../../google-cloud-php-v2-fixer/src/NewSurfaceFixer.php';
require_once __DIR__ . '/vendor/autoload.php';

return (new PhpCsFixer\Config())
    // ...
    ->registerCustomFixers([
        new Google\Cloud\Tools\NewSurfaceFixer(),
    ])
    ->setRules([
        // ...
        'GoogleCloud/new_surface_fixer' => true,
        'ordered_imports' => true,
    ])
;
