<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PSR2' => true,
    '@PHP81Migration' => true,
    'blank_line_after_opening_tag' => true,
    'class_reference_name_casing' => true,
    'combine_consecutive_issets' => true,
    'compact_nullable_typehint' => true,
    'linebreak_after_opening_tag' => true,
    'lowercase_cast' => true,
    'no_blank_lines_after_class_opening' => true,
    'no_unused_imports' => true,
    'no_whitespace_in_blank_line' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
    ],
    'single_blank_line_before_namespace' => true,
    'single_quote' => true,
    'standardize_not_equals' => true,
    'trim_array_spaces' => true,
    'trailing_comma_in_multiline' => [
        'after_heredoc' => true,
        'elements' => [
            'arrays',
            'arguments',
        ],
    ],
    'whitespace_after_comma_in_array' => true,
];


$finder = Finder::create()
    ->in([
        __DIR__.'/helpers',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new Config();

return $config->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
