<?php

# NOTE: You will need to download GeSHi by yourself, it does NOT come bundled-in!
# Download GeSHi at http://qbnz.com/highlighter/
require 'geshi/geshi.php';

spl_autoload_register(function($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = '../src/' . $file . '.php';

    if (is_file($file)) {
        include $file;
    }
});

$dtd = require '../src/dtd.php';

$string = '
This example demonstrates how to apply syntax coloring into the code tag using
[url=http://qbnz.com/highlighter/]GeSHi[/url].

[b]You need to download GeSHi before you can run this example![/b]

[code=php]
<?php
$hello = "Hello, world";
$elloh = "";

print $hello . PHP_EOL;

for ($i = 0; $i < strlen($hello); $i++) {
    $elloh = $hello[$i] . $elloh;
}

print $elloh . PHP_EOL;
?>
[/code]

[code=cpp]
#include <stdio>

int main() {
    std::cout << "Hello, world" << std::endl;
    return 0;
}
[/code]

[code]
The [b]bbcode[/b] inside this [color=red]section[/color] should not be parsed
because parsing contents is disabled for code tag in the DTD.
[/code]
';

$bbcode = new BBCode\Parser($dtd);

function bbcode_syntax_coloring($content, $language, $tag) {
    if (!$language) {
        return $content;
    }

    // Decode content because BBCode compiler encodes it automaticly.
    $content = htmlspecialchars_decode($content);

    $header = '<span class="bbcode-code-header">Language: ' . htmlspecialchars($language) . '</span>';

    $geshi = new GeSHi($content, $language);
    $geshi->set_header_type(GESHI_HEADER_NONE);
    $geshi->set_tab_width(4);

    $content = $geshi->parse_code();
    $content = str_replace(array("\n", "\r"), "", $content);

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
            <?= $bbcode->compile($string) ?>
        </div>
    </article>
</body>
</html>
