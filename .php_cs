<?php

$finder = PhpCsFixer\Finder::create()
    ->notPath('appengine/wordpress/src/files/flexible/wp-config.php')
    ->notPath('appengine/wordpress/src/files/standard/wp-config.php')
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR2' => true,
        'concat_with_spaces' => true,
        'no_unused_imports' => true,
        'no_trailing_whitespace' => true,
        'no_tab_indentation' => true,
    ))
    ->finder($finder);
