<?php

$header = <<< EOF
This file is part of the Karla package.

(c) Sankar Suda <sankar.suda@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code
EOF;

$finder = \PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src/')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return \PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules(array(
        '@Symfony' => true,
        'align_equals' => true,
        'align_double_arrow' => true,
        'short_array_syntax' => true,
        'ordered_imports' => true,
        'no_useless_return' => true,
        'phpdoc_order' => true,
        'no_short_echo_tag' => true,
        'header_comment' => array('header' => $header),
        'combine_consecutive_unsets' => true,
        'unalign_double_arrow' => false,
        'unalign_equals' => false,
    ))
    ->finder($finder)
;
