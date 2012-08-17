<?php

spl_autoload_register(function($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = '../src/' . $file . '.php';

    if (is_file($file)) {
        include $file;
    }
});

$dtd = require '../src/dtd.php';

$string = '
This example demonstrates how to [b]customize[/b] quotes.

[quote=FooBar]
    [quote=TestMan]This message was written by [color=green]somebody[/color] else![/quote]
    So was this message!
[/quote]
';

$bbcode = new BBCode\Parser($dtd);

function bbcode_format_quotes($content, $user, $tag) {
    $content = trim($content);
    $header = "<header class=\"bbcode-quote-header\">Original author <span class=\"username\">{$user}</span></header>";
    
    return $header . $content;
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>BBCode Compiler Example</title>
    <meta name="author" content="Samu Juvonen"/>

    <style type="text/css">
        @import url('css/demo.css');
        @import url('../src/bbcode.css');
    </style>
</head>
<body>
    <article class="message">
        <header>
            <span class="username">TestMan</span>
            <span class="datetime">2012-08-15 20:45</span>
        </header>
        <div class="content">
            <?= nl2br($bbcode->compile($string)) ?>
        </div>
    </article>
</body>
</html>
