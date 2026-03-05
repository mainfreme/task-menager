<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'no_unused_imports' => true,
        'blank_lines_before_namespace' => false,
        'single_blank_line_before_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'no_extra_blank_lines' => [
            'tokens' => ['extra', 'throw', 'use'],
        ],
        'single_line_throw' => false,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => false,
        'native_function_invocation' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
