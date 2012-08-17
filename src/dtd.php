<?php
/**
 * Example instructions for the BBCode compiler.
 **/

return array(
    'b' => array(
        'tag' => 'b',
        'attributes' => array(
            'class' => 'bbcode-b',
        ),
    ),

    'i' => array(
        'tag' => 'span',
        'attributes' => array(
            'class' => 'bbcode-i',
        ),
    ),

    'u' => array(
        'tag' => 'span',
        'attributes' => array(
            'class' => 'bbcode-u',
        ),
    ),

    'url' => array(
        'tag' => 'a',
        'attributes' => array(
            'class' => 'bbcode-url',
            'href' => '{value}',
        ),
    ),

    'color' => array(
        'tag' => 'span',
        'nested' => true,
        'attributes' => array(
            'class' => 'bbcode-color',
            'style' => 'color: {value}',
        ),
    ),

    'img' => array(
        'tag' => 'img',
        'attributes' => array(
            'class' => 'bbcode-img',
            'src' => '{content}',
        ),

        'options' => array(
            'empty' => true,
        ),
    ),

    'size' => array(
        'tag' => 'span',
        'attributes' => array(
            'class' => 'bbcode-size size-{value}',
        ),
    ),

    'code' => array(
        'tag' => 'code',
        'attributes' => array(
            'class' => 'bbcode-code',
        ),
        'options' => array(
            'parse_contents' => false,
            'callback' => 'bbcode_syntax_coloring',
        ),
    ),

    'quote' => array(
        'tag' => 'blockquote',
        'nested' => true,
        'attributes' => array(
            'class' => 'bbcode-quote',
        ),
        'options' => array(
            'callback' => 'bbcode_format_quotes',
        ),
    ),
);
