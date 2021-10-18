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
        ],
        // only converts simple strings in double quotes to single quotes
        // ignores strings using variables, escape characters or single quotes inside
        'single_quote' => true,
        // there should be a single space b/w the cast and it's operand
        'cast_spaces' => ['space' => 'single'],
        // there shouldn't be any trailing whitespace at the end of a non-blank line
        'no_trailing_whitespace' => true,
        // there shouldn't be any trailing whitespace at the end of a blank line
        'no_whitespace_in_blank_line' => true,
        // there should be a space around binary operators like (=, => etc)
        'binary_operator_spaces' => ['default' => 'single_space'],
        // deals with rogue empty blank lines
        'no_extra_blank_lines' => ['tokens' => ['extra']],
        // reduces multi blank lines b/w phpdoc description and @param to a single line
        // NOTE: Doesn't add a blank line if none exist
        'phpdoc_trim_consecutive_blank_line_separation' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    )
;

return $config;
