<?php
/*
 *  This file is part of typing/types.
 *
 *  (c) Victor Passapera <vpassapera@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

$finder = (new PhpCsFixer\Finder())
    ->in([
        'src',
        'tests'
    ])
    ->exclude([
        'bin',
        'build',
        'docs',
        'vendor'
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'space_after_semicolon' => false,
        'single_line_throw' => false,
        'no_superfluous_phpdoc_tags' => false,
        'class_definition' => ['single_line' => false],
    ])
    ->setFinder($finder)
;
