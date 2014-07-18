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



    protected function parseBlock (array $textlines = array()) {

        require_once __DIR__ . '/DocBlock.php';
        $doc = new DocBlock();



        return $doc;
    }
} 