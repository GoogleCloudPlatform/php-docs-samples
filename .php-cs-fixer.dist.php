<?php

// php-cs-fixer 3.0 distributed config file

$config = new PhpCsFixer\Config();
$config
    ->setRules([
        '@PSR2' => true,
        'concat_space' => ['spacing' => 'one'],
        'no_unused_imports' => true,
        'whitespace_after_comma_in_array' => true,
        'method_argument_space' => [
            'keep_multiple_spaces_after_comma' => true,
            'on_multiline' => 'ignore'
        ],
        'return_type_declaration' => [
            'space_before' => 'none'
        ]
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    )
;

return $config;
