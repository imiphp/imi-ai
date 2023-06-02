<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'                   => true,
        '@Symfony:risky'             => true,
        'php_unit_dedicate_assert'   => ['target' => '5.6'],
        'array_syntax'               => ['syntax' => 'short'],
        'array_indentation'          => true,
        'binary_operator_spaces'     => [
            'operators' => [
                '=>' => 'align_single_space',
            ],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'fopen_flags'                  => false,
        'protected_to_private'         => false,
        'native_function_invocation'   => true,
        'native_constant_invocation'   => true,
        'combine_nested_dirname'       => true,
        'single_quote'                 => true,
        'single_space_after_construct' => [
            'constructs' => ['abstract', 'as', 'attribute', 'break', 'case', 'catch', 'class', 'clone', 'comment', 'const', 'const_import', 'continue', 'do', 'echo', 'else', 'elseif', 'extends', 'final', 'finally', 'for', 'foreach', 'function', 'function_import', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'match', 'named_argument', 'new', 'open_tag_with_echo', 'php_doc', 'php_open', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'throw', 'trait', 'try', 'use', 'use_lambda', 'use_trait', 'var', 'while', 'yield', 'yield_from'],
        ],
        'braces'                     => [
            'position_after_control_structures' => 'next',
        ],
        'single_line_comment_style'  => false,
        'phpdoc_to_comment'          => false,
        'declare_strict_types'       => true,
        'heredoc_indentation'        => [
            'indentation' => 'same_as_start',
        ],
        'no_trailing_whitespace_in_string' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude([
                'Module/Pay/PayService',
                'Module/Account/AccountService',
                'Module/Common/Grpc',
            ])
            ->in(__DIR__)
            ->append([__FILE__])
    )
;
