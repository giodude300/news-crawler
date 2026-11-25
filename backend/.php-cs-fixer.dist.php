
<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'cache',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,

        'array_syntax' => [
            'syntax' => 'short',
        ],

        'braces_position' => [
            'functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'same_line',
            'anonymous_classes_opening_brace' => 'same_line',
            'control_structures_opening_brace' => 'same_line',
        ],

        'indentation_type' => true,
        'no_unused_imports' => true,

        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],

        'single_quote' => true,

        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'match',
            ],
        ],
    ])
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder($finder);
