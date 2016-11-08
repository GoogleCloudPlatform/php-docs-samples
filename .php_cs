<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath('appengine/wordpress/src/files/flexible/wp-config.php')
    ->notPath('appengine/wordpress/src/files/standard/wp-config.php')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'concat_with_spaces' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
;
