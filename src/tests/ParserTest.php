<?php
/**
 * User: matt
 * Date: 18/07/14
 * Time: 12:20
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../AnsibleDoc/Parser.php';
require_once __DIR__ . '/../AnsibleDoc/DocBlock.php';


class ParserTest extends PHPUnit_Framework_TestCase {




    public function test_no_docblocks_returns_nothing () {
        $test = <<<BLOCK
# just a comment
BLOCK;

        $parser = new AnsibleDoc\Parser();
        $blocks = $parser->parse($test);

        $this->assertEquals(0, count($blocks));
    }



    public function test_we_can_parse_a_single_simple_docblock () {

        $test = <<<BLOCK
###
#
# Main description
###
BLOCK;

        $parser = new AnsibleDoc\Parser();
        $blocks = $parser->parse($test);

        $this->assertEquals(1, count($blocks));
        $block0 = $blocks[0];
        $this->assertInstanceOf('AnsibleDoc\DocBlock', $block0);
        $this->assertEquals('Main description', $block0->getShortDescription());
    }



    public function test_we_can_extract_two_docblocks () {

        $test = <<<BLOCK
###
#
# Main description
###

###
# Second block short desc
###
BLOCK;

        $parser = new AnsibleDoc\Parser();
        $blocks = $parser->parse($test);

        $this->assertEquals(2, count($blocks));
    }



    public function test_we_can_parse_a_second_simple_docblock () {

        $test = <<<BLOCK
###
#
# The short description
###
BLOCK;

        $parser = new AnsibleDoc\Parser();
        $blocks = $parser->parse($test);

        $this->assertEquals(1, count($blocks));
        $block0 = $blocks[0];
        $this->assertInstanceOf('AnsibleDoc\DocBlock', $block0);
        $this->assertEquals('The short description', $block0->getShortDescription());
    }


    public function test_we_can_parse_a_full_file_with_multi_docblocks_and_tags () {

        $text = <<<BLOCK
---

###
#
# Short Desc 1
#
# This is the longer description.
#
# With multi-lines
#
# @author Matt Parker
# @since 1.0
# @anything All tags allowed!
# and you can do
# multi line tags
# @lastly hi
#
###


###
#
# DB2
#
# Pings all hosts to find out something
#
# @copyright Lamplight
###
- hosts: all
  tasks:
    - name: whatever
      ping:


###
#
# Third doc block
#
# # Another thing - markdown?
#
# - a list ish
# - num 2
#
###

BLOCK;


        $parser = new AnsibleDoc\Parser();
        $blocks = $parser->parse($text);

        $this->assertEquals(3, count($blocks));

        $block0 = $blocks[0];

        $this->assertEquals('Short Desc 1', $block0->getShortDescription());
        $this->assertEquals(
            'This is the longer description.' . PHP_EOL . PHP_EOL . 'With multi-lines',
            $block0->getLongDescription()
        );
        $this->assertEquals(['Matt Parker'], $block0->getTag('author'));
        $this->assertEquals(['1.0'], $block0->getTag('since'));
        $this->assertEquals(['All tags allowed!' . PHP_EOL . 'and you can do' . PHP_EOL . 'multi line tags'], $block0->getTag('anything'));
        $this->assertEquals(['hi'], $block0->getTag('lastly'));


        $block1 = $blocks[1];
        $this->assertEquals('DB2', $block1->getShortDescription());
        $this->assertEquals(
            'Pings all hosts to find out something',
            $block1->getLongDescription()
        );
        $this->assertEquals(['Lamplight'], $block1->getTag('copyright'));


        $block2 = $blocks[2];
        $this->assertEquals('Third doc block', $block2->getShortDescription());
        $this->assertEquals(
            '# Another thing - markdown?' . PHP_EOL . PHP_EOL . '- a list ish' . PHP_EOL . '- num 2',
            $block2->getLongDescription()
        );

    }

}
 