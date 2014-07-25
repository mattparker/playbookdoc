#!/usr/bin/php
<?php
/**
 *
 * Documentation tool for Ansible playbooks and related files.
 *
 * @copyright Matt Parker, Lamplight Database Systems Limited 2014
 * @license BSD
 * @version 0.1
 * @link https://github.com/mattparker/ansibledoc
 *
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

// Recurse through the directory
$dir = new RecursiveDirectoryIterator($options['i']);
$rec_iterator = new RecursiveIteratorIterator($dir);
$file_list = new RegexIterator($rec_iterator, '/^.+\.yml$/i', RecursiveRegexIterator::GET_MATCH);

// setup the output
$out = new AnsibleDoc\HtmlOutput($options['o']);

// parse each file for docblocks
foreach ($file_list as $name => $file_ob) {

    $parser = new AnsibleDoc\Parser();
    $name_from_here = str_replace($options['i'], '', $name);
    $out->addFileResults($name_from_here, $parser->parse(file_get_contents($name)));

}


// write out the results
$out->write();
