<?php
/**
 * User: matt
 * Date: 18/07/14
 * Time: 15:11
 */

namespace AnsibleDoc;


/**
 * Class HtmlOutput
 *
 * Generates a series of html files from the parsed docblocks of .yml files.
 *
 * @package AnsibleDoc
 */
class HtmlOutput {


    /**
     * @var string
     */
    protected $dir;


    /**
     * @var array
     */
    protected $parsedFiles = [];

    /**
     * Template for main page
     * @var string
     */
    protected $template = <<<TEMPLATE
 <html><head><title>Ansible Docs: {filename}</title></head>
 <body><h1>{filename}</h1>
 {content}
 </body></html>
TEMPLATE;



    /**
     * Template for a doc block within a page
     * @var string
     */
    protected $blockTemplate = <<<BLOCKTEMPLATE
<div class="block block-{block_num}">
 <h2>{short_description}</h2>
 <p>{long_description}</p>
 <dl>{tags}</dl>
</div>
BLOCKTEMPLATE;


    /**
     * Template for a single tag
     * @var string
     */
    protected $tagTemplate = <<<TAGTEMPLATE
<dt>{tag_name}</dt>
<dd>{tag_value}</dd>
TAGTEMPLATE;


    /**
     * @param string $directory Where to write docs to
     */
    public function __construct ($directory) {
        $this->dir = $directory;
    }


    /**
     * @param string $filename
     * @param array $docblocks
     */
    public function addFileResults ($filename, array $docblocks = array()) {

        $this->parsedFiles[$filename] = $docblocks;
    }


    /**
     * Prepare and write html files
     */
    public function write () {

        foreach ($this->parsedFiles as $filename => $docblocks) {

            $content = $this->writeContent($docblocks);

            $template = str_replace(
                ['{filename}', '{content}'],
                [$filename, $content],
                $this->template
            );


            $saveFile = $this->convertFilenameToDocFilename($filename);
            file_put_contents($this->dir . DIRECTORY_SEPARATOR . $saveFile, $template);
        }
    }


    /**
     * @param DocBlock[] $docblocks
     *
     * @return string
     */
    protected function writeContent (array $docblocks) {

        $ret = '';
        $i = 1;

        foreach ($docblocks as $block) {

            $short = $block->getShortDescription();
            $long = $block->getLongDescription();

            $tags = $this->prepareTags($block);

            $ret .= str_replace(
                ['{short_description}', '{long_description}', '{tags}', '{block_num}'],
                [$short, $long, $tags, $i],
                $this->blockTemplate
            );

            $i++;

        }

        return $ret;
    }



    /**
     * @param $block
     *
     * @return string
     */
    protected function prepareTags (DocBlock $block) {

        $tags_used = $block->getTagsUsed();

        if (count($tags_used) === 0) {
            return '';
        }

        $tags = '';

        foreach ($tags_used as $tagname) {

            $tagvalue = $this->prepareTag($tagname, $block->getTag($tagname));

            $tags .= str_replace(
                ['{tag_name}', '{tag_value}'],
                [$tagname, $tagvalue],
                $this->tagTemplate
            );

        }

        return $tags;


    }


    /**
     * @param $tagname
     * @param $tagvalue
     *
     * @return string
     */
    protected function prepareTag ($tagname, $tagvalue) {


        switch ($tagname) {

            // Special case for 'role' tags: link to the docs for them
            case 'role':

                $ret = [];
                foreach ($tagvalue as $tv) {

                    $url = $this->findRoleUrl($tv);
                    $ret[] = $this->link($tv, $url);

                }

                break;


            default:
                $ret = $tagvalue;
                break;
        }

        return $this->joinLines($ret);

    }



    /**
     * @param array $lines
     *
     * @return string
     */
    protected function joinLines (array $lines) {
        return implode('<br>', $lines);
    }



    /**
     * Create a link
     *
     * @param $text
     * @param $url
     *
     * @return string
     */
    protected function link ($text, $url = '') {
        if ($url === '') {
            return $text;
        }
        return '<a href="./' . $url . '">' . $text . '</a>';
    }




    /**
     * If we're using 'role' tags, look and see if we can link to other
     * roles
     *
     * @param $rolename
     *
     * @return string
     */
    protected function findRoleUrl ($rolename) {

        $rolename = '/roles/' . $rolename;
        $rolename_length = strlen($rolename);

        foreach ($this->parsedFiles as $filename => $docblocks) {

            if (substr($filename, 0, $rolename_length) === $rolename) {

                if (array_key_exists($rolename . '/tasks/main.yml', $this->parsedFiles)) {
                    return $this->convertFilenameToDocFilename($rolename . '/tasks/main.yml');
                }
                return $this->convertFilenameToDocFilename($filename);
            }

        }
        return '';
    }

    /**
     * Take a filename from the local system and convert it into a flat filename
     *
     * @param $filename
     *
     * @return string
     */
    protected function convertFilenameToDocFilename ($filename) {
        return substr(str_replace(DIRECTORY_SEPARATOR, '_', $filename), 1) . '.html';
    }

}
