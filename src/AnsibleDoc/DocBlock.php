<?php
/**
 * User: matt
 * Date: 18/07/14
 * Time: 12:32
 */

namespace AnsibleDoc;


/**
 * Class DocBlock
 * @package AnsibleDoc
 */
class DocBlock {



    /**
     * @var array
     */
    protected $values = array(
        'tags' => []
    );

    protected $lastTagSet;


    /**
     * Repeated calls to this method will sets things in order.
     *
     * The first call with a text string (ie not an @tag) will set the short description.
     * Subsequent calls (with no @tags) will set the long description.
     * A call with a line starting @something value will set the 'something' tag.
     * Each tag can have multiple values.
     *
     * @param $text
     */
    public function parseLine ($text) {

        $text = trim($text);

        if (substr($text, 0, 1) === '@') {

            $this->setTag($text);

        } else {

            if ($text != '' && $this->lastTagSet !== null) {

                $this->appendToLastTagSet($text);

            } else if ($text != '' && !array_key_exists('short_description', $this->values)) {

                $this->values['short_description'] = $text;
                $this->lastTagSet = null;

            } else {

                if (!array_key_exists('long_description', $this->values)) {
                    $this->values['long_description'] = $text;
                } else {
                    $this->values['long_description'] .= PHP_EOL . $text;
                }
                $this->lastTagSet = null;
            }
        }
    }


    /**
     * @return string
     */
    public function getShortDescription () {

        if (array_key_exists('short_description', $this->values)) {
            return $this->values['short_description'];
        }
        return '';

    }


    /**
     * @return string
     */
    public function getLongDescription () {

        if (array_key_exists('long_description', $this->values)) {
            return $this->values['long_description'];
        }
        return '';

    }


    /**
     * @param $tagname
     *
     * @return array
     */
    public function getTag ($tagname) {

        if (array_key_exists($tagname, $this->values['tags'])) {
            return $this->values['tags'][$tagname];
        }
        return [];
    }


    /**
     * @param $text
     */
    protected function setTag ($text) {

        $space = strpos($text, ' ');
        $tagname = trim(substr($text, 1, $space));
        $tagvalue = trim(substr($text, $space));

        if (!array_key_exists($tagname, $this->values['tags'])) {
            $this->values['tags'][$tagname] = [];
        }

        $this->values['tags'][$tagname][] = $tagvalue;

        $this->lastTagSet = $tagname;

    }


    /**
     * Allows for multi-line tags
     * @param $text
     */
    protected function appendToLastTagSet ($text) {

        $numvals = count($this->values['tags'][$this->lastTagSet]);
        $this->values['tags'][$this->lastTagSet][$numvals - 1] .= PHP_EOL . $text;

    }

}
