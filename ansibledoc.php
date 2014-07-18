#!/usr/bin/php
<?php
/**
 * User: matt
 * Date: 18/07/14
 * Time: 12:16
 */

require_once __DIR__ . '/src/AnsibleDoc/Parser.php';
require_once __DIR__ . '/src/AnsibleDoc/HtmlOutput.php';

// options

/*
 -i directory to start from. Assumes these are playbooks
 -o where to write html to
*/

$options = getopt('i::o::', []);

if (!array_key_exists('i', $options)) {
    $options['i'] = getcwd();
}
if (!array_key_exists('o', $options) && is_dir($options['i'] . '/docs')){
    $options['o'] = $options['i'] . '/docs';
}

$dir = new RecursiveDirectoryIterator($options['i']);
$rec_iterator = new RecursiveIteratorIterator($dir);
$file_list = new RegexIterator($rec_iterator, '/^.+\.yml$/i', RecursiveRegexIterator::GET_MATCH);


$out = new AnsibleDoc\HtmlOutput($options['o']);


foreach ($file_list as $name => $file_ob) {

    $parser = new AnsibleDoc\Parser();
    $name_from_here = str_replace($options['i'], '', $name);
    $out->addFileResults($name_from_here, $parser->parse(file_get_contents($name)));


}

$out->write();