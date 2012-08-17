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
This is an example about how the default compiler instructions work.

Lorem ipsum dolor sit amet, [b]consectetur [u]adipiscing[/u] elit[/b]. Nam laoreet elit et ligula malesuada eleifend. [u]Etiam tempor dolor at velit[/u] condimentum sed tincidunt lacus vulputate. [i]Mauris vitae tellus nisi[/i], sed rhoncus est. Phasellus tristique [color=green]dolor [b]et[/b] enim[/color] viverra eget rutrum nulla malesuada. Proin rutrum porta nisi eget suscipit. Curabitur scelerisque justo at elit porttitor feugiat.

In a [url=http://www.google.com]magna id mauris elementum[/url] malesuada sed consequat enim. Mauris aliquam justo eget odio varius semper. Nullam pretium lectus non est sodales consequat. In hac habitasse platea dictumst. Donec accumsan faucibus ipsum ac sagittis. Vivamus vel justo id leo bibendum luctus. Nullam ultrices tincidunt tortor, porta lacinia turpis volutpat eget. Suspendisse lobortis commodo pretium. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.

[size=6]Here is some large text[/size]
[size=2]Here is some small text[/size]

<b>This HTML formatting <i>should</i> be encoded...</b>

[url=http://www.gnu.org/]
    [img]images/heckert_gnu.png[/img]
    GNU is nice!
[/url]
';

$bbcode = new BBCode\Parser($dtd);

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
