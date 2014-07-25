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
 <html><head><title>Ansible Docs: {filename}</title>
 <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
 <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/grids-responsive-min.css">
 <style type="text/css">.main {margin-left:1em;}
 h1 {font-size: 150%; background-color: #3355BA; color: #FFF; margin: 0; padding: 1em;}
 .block {font-size:90%; margin: 2em 0.3em 1em 3em;} .block-1{font-size: 100%; margin-left: 1em;}
 .contents {background-color: #eee7dd;color: #14100a; padding: 1px 0 0 0.6em;}
 a, a:link {color:#b59569;}
 dl {margin-top: 1em; padding-bottom: 1em;border-bottom: 1px solid #b59569;}
 dd {}
 dt {font-family: monospace;}
 </style>
 </head>
 <body>
 <div class="pure-g">
     <div class="pure-u-1 pure-u-md-3-4">
     <div class="main">
     <h1>{filename}</h1>
     {content}
     </div>
     </div>
     <div class=" pure-u-1 pure-u-md-1-4">
      <div class="contents">
      <h2>Contents</h2>{content_listing}</div>
     </div>
 </div>
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


    protected $local_vars;
    protected $local_plays;
    protected $local_roles;

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

        $contents = $this->prepareContents();


        foreach ($this->parsedFiles as $filename => $docblocks) {

            $content = $this->writeContent($docblocks);

            $template = str_replace(
                ['{filename}', '{content}', '{content_listing}'],
                [$filename, $content, $contents],
                $this->template
            );


            $saveFile = $this->convertFilenameToDocFilename($filename);
            file_put_contents($this->dir . DIRECTORY_SEPARATOR . $saveFile, $template);
        }
    }


    /**
     * Makes the contents page
     *
     * @return string
     */
    protected function prepareContents () {

        $roles = [];
        $plays = [];
        $vars = [];

        foreach ($this->parsedFiles as $filename => $docblocks) {

            if (preg_match('/^\/roles\/([^\/]+)\//', $filename, $matches)) {
                // This is a role
                // looking for starts with "/roles/.../more" - where ... is what we want
                $roles[$matches[1]][] = $filename;

            } else if (preg_match('/^\/([^\/]+).yml$/', $filename, $matches)) {

                // This is a play
                // looking for 'not a forward slash' followed by .yml
                $plays[] = $matches[1];

            } else if (preg_match('/^\/((host_|group_)vars)/', $filename, $matches)) {

                // This is a vars
                // looking for host_vars or group_vars
                $vars[$matches[1]][] = $filename;
            }

        }



        $ret = $this->renderContentsPlays($plays);

        $ret .= $this->renderContentsRoles($roles);

        $ret .= $this->renderContentsVars($vars);


        return $ret;

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

    /**
     * @param $plays
     *
     * @return string
     */
    protected function renderContentsPlays ($plays) {
        $ret = '<div class="plays"><h4>Plays</h4><ul>';
        foreach ($plays as $playname) {
            $ret .= '<li>' . $this->link($playname, $this->convertFilenameToDocFilename('/' . $playname . '.yml'));
        }
        $ret .= '</ul></div>';
        return $ret;
    }

    /**
     * @param $roles
     *
     * @return string
     */
    protected function renderContentsRoles (array $roles) {

        $ret = '<div class="roles"><h4>Roles</h4><ul>';
        foreach ($roles as $role_name => $filenames) {
            $ret .= '<li><h5>' . $role_name . '</h5><ul>';
            foreach ($filenames as $filename) {
                $ret .= '<li>' . $this->link(
                        str_replace('/roles/' . $role_name . '/', '', $filename),
                        $this->convertFilenameToDocFilename($filename)) . '</li>';
            }
            $ret .= '</ul>';
        }
        $ret .= '</ul></div>';
        return $ret;
    }

    /**
     * @param $vars
     *
     * @return string
     */
    protected function renderContentsVars ($vars) {
        $ret = '<div class="vars"><h4>Variables</h4><ul>';
        foreach ($vars as $var_type => $var_files) {
            $ret .= '<li><h5>' . $var_type . '</h5><ul>';
            foreach ($var_files as $filename) {
                $ret .= '<li>' . $this->link(
                        str_replace('/' . $var_type . '/', '', $filename),
                        $this->convertFilenameToDocFilename($filename)) . '</li>';
            }
            $ret .= '</ul>';
        }
        $ret .= '</ul></div>';
        return $ret;
    }

}
