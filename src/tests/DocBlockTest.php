<?php
/**
 * User: matt
 * Date: 18/07/14
 * Time: 13:53
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../AnsibleDoc/Parser.php';
require_once __DIR__ . '/../AnsibleDoc/DocBlock.php';


class DocBlockTest extends PHPUnit_Framework_TestCase {


    public function test_the_first_thing_set_sets_the_short_description () {

        $text = 'abcc';

        $doc = new AnsibleDoc\DocBlock();
        $doc->parseLine($text);

        $this->assertEquals($text, $doc->getShortDescription());
    }


    public function test_the_second_line_sets_the_long_description () {

        $doc = new AnsibleDoc\DocBlock();

        $doc->parseLine('a');

        $longdesc = 'b';
        $doc->parseLine($longdesc);

        $this->assertEquals($longdesc, $doc->getLongDescription());
    }

    public function test_a_two_line_long_desc_gets_parsed () {

        $doc = new AnsibleDoc\DocBlock();

        $doc->parseLine('a');

        $longdesc1 = 'b';
        $doc->parseLine($longdesc1);

        $longdesc2 = 'c';
        $doc->parseLine($longdesc2);

        $this->assertEquals($longdesc1 . PHP_EOL . $longdesc2, $doc->getLongDescription());

    }


    public function test_we_can_get_a_tag () {

        $doc = new AnsibleDoc\DocBlock();
        $text = '@author Matt';
        $doc->parseLine($text);

        $this->assertEquals(['Matt'], $doc->getTag('author'));

    }

    public function test_we_can_get_two_tags_with_same_name () {

        $doc = new AnsibleDoc\DocBlock();
        $text = '@author Matt';
        $doc->parseLine($text);
        $text = '@author Jen';
        $doc->parseLine($text);

        $this->assertEquals(['Matt', 'Jen'], $doc->getTag('author'));

    }

    public function test_we_can_parse_multi_line_tags () {
        $doc = new AnsibleDoc\DocBlock();
        $text = '@author Matt';
        $doc->parseLine($text);
        $text2 = 'Lives in London';
        $doc->parseLine($text2);

        $this->assertEquals(['Matt' . PHP_EOL . $text2], $doc->getTag('author'));
    }


}
 