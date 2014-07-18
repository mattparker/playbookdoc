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
}
 