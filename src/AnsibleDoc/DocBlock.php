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


    /**
     * The last tag (name) set, if any
     *
     * @var string
     */
    protected $lastTagSet = null;


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
            return;

        }


        if ($this->lastTagSet !== null) {

            $this->appendToLastTagSet($text);
            return;

        }


        if ($text != '' && !$this->hasShortDescription()) {

            $this->values['short_description'] = $text;
            $this->lastTagSet = null;
            return;

        }


        $this->setLongDescription($text);
        $this->lastTagSet = null;


    }


    /**
     * @return string
     */
    public function getShortDescription () {

        if ($this->hasShortDescription()) {
            return $this->values['short_description'];
        }
        return '';

    }


    /**
     * @return string
     */
    public function getLongDescription () {

        if ($this->hasLongDescription()) {
            return trim($this->values['long_description']);
        }
        return '';

    }


    /**
     * @param string $tagname
     *
     * @return array
     */
    public function getTag ($tagname) {

        if ($this->hasTag($tagname)) {
            return $this->values['tags'][$tagname];
        }
        return [];
    }


    /**
     * @return array
     */
    public function getTagsUsed () {
        return array_keys($this->values['tags']);
    }


    /**
     * @param string $text
     */
    protected function setTag ($text) {

        $space = strpos($text, ' ');
        $tagname = trim(substr($text, 1, $space));
        $tagvalue = trim(substr($text, $space));

        if (!$this->hasTag($tagname)) {
            $this->values['tags'][$tagname] = [];
        }

        $this->values['tags'][$tagname][] = $tagvalue;

        $this->lastTagSet = $tagname;

    }


    /**
     * Allows for multi-line tags
     * @param string $text
     */
    protected function appendToLastTagSet ($text) {

        $lastTag =& $this->values['tags'][$this->lastTagSet];
        $numvals = count($lastTag);
        $lastTag[$numvals - 1] .= PHP_EOL . $text;
        $lastTag[$numvals - 1] = trim($lastTag[$numvals - 1]);

    }


    /**
     * @return bool
     */
    public function hasShortDescription () {
        return array_key_exists('short_description', $this->values);
    }


    /**
     * @return bool
     */
    public function hasLongDescription () {
        return array_key_exists('long_description', $this->values);
    }


    /**
     * @param $tagname
     *
     * @return bool
     */
    public function hasTag ($tagname) {
        return array_key_exists($tagname, $this->values['tags']);
    }


    /**
     * Sets or appends, depending on whether there's something there already.
     *
     * @param $text
     */
    protected function setLongDescription ($text) {

        if (!$this->hasLongDescription()) {
            if ($text !== '') {
                $this->values['long_description'] = $text;
            }
        } else {
            $this->values['long_description'] .= PHP_EOL . $text;
        }

    }

}
