<?php
/**
 * User: matt
 * Date: 18/07/14
 * Time: 12:16
 */


namespace AnsibleDoc;


class Parser {


    /**
     * Parses text string for docblocks and extracts the relevant info
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