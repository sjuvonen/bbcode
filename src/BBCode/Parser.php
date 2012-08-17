<?php

namespace BBCode;

/**
 * Programmable BBCode parser / renderer.
 *
 * The BBCode parser can be configured using a simple array to suit your needs
 * or be compatible with another BBCode system.
 *
 * @author Samu Juvonen <samu.juvonen@gmail.com>
 * @since 2012-08-17
 * @version 2.0
 **/
class Parser {
    private $dtd;
    private $source;
    private $encode_html;

    /**
     * Constructor
     *
     * @param $dtd associative array of compiler instructions
     * @param $encode_html whether or not to make the string HTML-safe
     **/
    public function __construct(array $dtd, $encode_html = true) {
        $this->encode_html = (bool)$encode_html;

        $defaults = array(
            // Corresponding HTML tag for the BBCode element
            'tag' => '',

            // HTML attributes that will be written into the element
            'attributes' => array(),

            // Special configuration for the compiler instruction
            'options' => array(

                // Run parser on the BBCode tag's contents or not
                'parse_contents' => true,

                // Is the resulting HTML element empty or not (use short end tag)
                'empty' => false,

                // Callable method that can perform additional transformations
                // on the element's contents
                // The function will get three arguments: content, value, tag
                // Example: [tag=value]content here[/tag]
                'callback' => null,
            ),
        );

        foreach ($dtd as $key => $data) {
            $this->dtd[$key] = $this->merge($data, $defaults);
        }
    }

    /**
     * Compile given string with BBCode to HTML.
     *
     * @param $string string to compile
     * @return string
     **/
    public function compile($string) {
        if ($this->encode_html) {
            $string = htmlspecialchars($string);
        }

        $compiled = $this->process($string);

        return $compiled;
    }

    private function process($string) {
        $string = trim($string);
        $tags = array_keys($this->dtd);
        $tag_string = implode('|', $tags);
        $regex = "#(\[({$tag_string})=?(.*?)?\])|(\[/({$tag_string})\])#s";

        preg_match_all($regex, $string, $matches, PREG_OFFSET_CAPTURE);

        $matches = $matches[0];
        $stack = array();
        $cache = array();
        $valid = array();
        $fix = 0;
        $ignore = 0;
        $replace = array();

        // Validate matches. Will filter out any tags who lack their opening/closing
        // pair or are closed in the wrong order.
        foreach ($matches as $i => $match) {
            list($tag, $offset) = $match;
            $name = $this->tagName($tag);
            
            if ($this->isOpenTag($tag)) {
                // Push opening tags into cache.
                
                $stack[] = $name;
                $cache[] = $match;
            }

            if ($this->isCloseTag($tag)) {
                // If a closing tag has a pair in the tag cache, accept the pair.
                // Any tags in the cache that come after the matching element
                // are considered to be invalid and are removed from the cache.
                
                $x = $this->findLastMatch($name, $stack);

                if ($x !== false) {
                    $valid[] = array($cache[$x], $match);
                    $stack = array_slice($stack, 0, $x);
                    $cache = array_slice($cache, 0, $x);
                }
            }
        }

        // Sort matches in the order of appearance of their opening tags.
        usort($valid, function($a, $b) { return $a[0][1] - $b[0][1]; });

        // Remove matches that are nested inside a "do not parse contents" element.
        foreach ($valid as $i => $pair) {
            $defs = $this->dtd[$this->tagName($pair[0][0])];

            if ($pair[1][1] < $ignore) {
                unset($valid[$i]);
            } elseif ($defs['options']['parse_contents'] == false) {
                $ignore = $pair[1][1];
            }
        }

        // Compile matches
        foreach ($valid as $pair) {
            list($a, $b) = $pair;
            $start = $a[1] + $fix;
            $length = $b[1] - $a[1];
            
            $raw = substr($string, $start, $length + strlen($b[0]));
            $content = substr($raw, strlen($a[0]), -1 * strlen($b[0]));
            $tag = $this->tagName($a[0]);
            $value = $this->tagValue($a[0]);
            $compiled = $this->compileTag(array($raw, $tag, $value, $content));

            $replace[] = array($raw, $compiled);
        }

        // Finally replace matches with compiled code.
        foreach ($replace as $pair) {
            $start = strpos($string, $pair[0]);
            $string = substr_replace($string, $pair[1], $start, strlen($pair[0]));
        }

        return $string;
    }

    private function tagName($tag) {
        preg_match('#^\[/?(\w+)#', $tag, $m);
        return $m[1];
    }

    private function tagValue($tag) {
        preg_match('#^\[\w+=(.*?)\]$#', $tag, $m);
        return isset($m[1]) ? $m[1] : null;
    }

    private function isOpenTag($tag) {
        return $tag[1] != '/';
    }

    private function isCloseTag($tag) {
        return !$this->isOpenTag($tag);
    }

    private function findLastMatch($needle, $haystack) {
        for ($i = count($haystack) - 1; $i >= 0; $i--) {
            if ($haystack[$i] == $needle) {
                return $i;
            }
        }

        return false;
    }

    private function compileTag($match) {
        list($raw, $tag, $value, $content) = $match;

        $dtd = $this->dtd[$tag];

        if ($dtd['options']['callback']) {
            $content = $dtd['options']['callback']($content, $value, $tag);
        }

        $markup = '<' . $dtd['tag'];

        foreach ($dtd['attributes'] as $name => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            $value = $this->compileValue($value, $match);
            $markup .= " {$name}=\"{$value}\"";
        }

        if ($dtd['options']['empty']) {
            $markup .= '/>';
        } else {
            $markup .= '>';
            $markup .= $content;
            $markup .= "</{$dtd['tag']}>";
        }

        return $markup;
    }

    private function compileValue($string, $match) {
        list($raw, $tag, $value, $content) = $match;

        $string = str_replace('{value}', $value, $string);
        $string = str_replace('{tag}', $tag, $string);
        $string = str_replace('{content}', $content, $string);

        return $string;
    }

    private function merge($target, $source) {
        foreach ($source as $key => $value) {
            if (!isset($target[$key]) || is_array($value) && !is_array($target[$key])) {
                $target[$key] = $value;
            }

            if (is_array($value)) {
                $target[$key] = $this->merge($target[$key], $value);
            }
        }

        return $target;
    }
}
