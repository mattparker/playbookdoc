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


namespace AnsibleDoc;


/**
 * Class Parser
 *
 * Parses text for docblocks in yml files.
 *
 * @package AnsibleDoc
 */
class Parser {


    /**
     * Parses text string for docblocks and extracts the relevant info
     *
     * Docblocks should start with three consecutive hash symbols (###),
     * each subsequent line should start with a has symbol (#),
     * and the docblock should be closed with three consecutive hash symbols (###).
     *
     * For example:

     ###
     #
     # This is a simple yaml style docblock
     #
     ###

     *
     * @param $text
     *
     * @return DocBlock[]
     */
    public function parse ($text) {

        $docblocks = $this->extractDocBlocks($text);
        $ret = [];

        foreach ($docblocks as $block) {
            $ret[] = $this->parseBlock($block);
        }

        return $ret;

    }


    /**
     * Finds docblocks
     *
     * @param $text
     *
     * @return DocBlock[]
     */
    protected function extractDocBlocks ($text) {

        $lines = explode(PHP_EOL, $text);
        $ret = [];

        $blockStarted = false;
        $block = [];

        foreach ($lines as $line) {

            $line = trim($line);

            if (substr($line, 0, 3) === '###') {

                if ($blockStarted) {
                    $ret[] = $block;
                    $blockStarted = false;
                } else {
                    $blockStarted = true;
                    $block = [];
                }

            } else if ($blockStarted) {
                // exclude the initial #
                $block[] = substr($line, 1);
            }
        }

        return $ret;
    }


    /**
     * Takes the text lines found in the block and parses it into
     * a AnsibleDoc\DocBlock
     * @param array $textlines
     *
     * @return DocBlock
     */
    protected function parseBlock (array $textlines = array()) {

        require_once __DIR__ . '/DocBlock.php';
        $doc = new DocBlock();

        foreach ($textlines as $line) {

            $doc->parseLine($line);

        }


        return $doc;
    }
} 