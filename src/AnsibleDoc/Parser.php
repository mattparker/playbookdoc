<?php
/**
 * User: matt
 * Date: 18/07/14
 * Time: 12:16
 */


namespace AnsibleDoc;


class Parser {


    public function parse ($text) {

        $docblocks = $this->extractDocBlocks($text);
        $ret = [];

        foreach ($docblocks as $block) {
            $ret[] = $this->parseBlock($block);
        }

        return $ret;

    }

    protected function extractDocBlocks ($text) {

        $lines = explode(PHP_EOL, $text);
        return [1];
    }

    protected function parseBlock ($text) {

        require_once __DIR__ . '/DocBlock.php';
        return new DocBlock();
    }
} 