<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * HTML renderer
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package    Text_Highlighter
 * @author     Andrey Demenev <demenev@gmail.com>
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    CVS: $Id: Html.php,v 1.2 2007/06/29 06:56:34 ssttoo Exp $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

/**
 * @ignore
 */

require_once dirname(__FILE__).'/../Renderer.php';
require_once dirname(__FILE__).'/../Renderer/Array.php';

// BC trick : only define constants if Text/Highlighter.php
// is not yet included
if (!defined('HL_NUMBERS_LI')) {
    /**#@+
     * Constant for use with $options['numbers']
     */
    /**
     * use numbered list, deprecated, use HL_NUMBERS_OL instaed
     * @deprecated
     */
    define ('HL_NUMBERS_LI'    ,    1);
    /**
     * Use 2-column table with line numbers in left column and code in  right column.
     */
    define ('HL_NUMBERS_TABLE'    , 2);
    /**#@-*/
}


/**#@+
 * Constant for use with $options['numbers']
 */
/**
 * Use numbered list
 */
define ('HL_NUMBERS_OL',    1);
/**
 * Use non-numbered list
 */
define ('HL_NUMBERS_UL',    3);
/**#@-*/


/**
 * HTML renderer
 *
 * Elements of $options argument of constructor (each being optional):
 *
 * - 'numbers' - Line numbering style 0 or {@link HL_NUMBERS_TABLE}
 *               or {@link HL_NUMBERS_UL} or {@link HL_NUMBERS_OL}
 * - 'numbers_start' - starting number for numbered lines
 * - 'tabsize' - Tab size
 * - 'style_map' - Mapping of keywords to formatting rules using inline styles
 * - 'class_map' - Mapping of keywords to formatting rules using class names
 * - 'doclinks' - array that has keys "url", "target" and "elements", used for
 *                generating links to online documentation
 * - 'use_language' - class names will be prefixed with language, like "php-reserved" or "css-code"
 *
 * Example of setting documentation links:
 * $options['doclinks'] = array(
 *   'url' => 'http://php.net/%s',
 *   'target' => '_blank',
 *   'elements' => array('reserved', 'identifier')
 * );
 *
 * Example of setting class names map:
 * $options['class_map'] = array(
 *       'main'       => 'my-main',
 *       'table'      => 'my-table',
 *       'gutter'     => 'my-gutter',
 *       'brackets'   => 'my-brackets',
 *       'builtin'    => 'my-builtin',
 *       'code'       => 'my-code',
 *       'comment'    => 'my-comment',
 *       'default'    => 'my-default',
 *       'identifier' => 'my-identifier',
 *       'inlinedoc'  => 'my-inlinedoc',
 *       'inlinetags' => 'my-inlinetags',
 *       'mlcomment'  => 'my-mlcomment',
 *       'number'     => 'my-number',
 *       'quotes'     => 'my-quotes',
 *       'reserved'   => 'my-reserved',
 *       'special'    => 'my-special',
 *       'string'     => 'my-string',
 *       'url'        => 'my-url',
 *       'var'        => 'my-var',
 * );
 *
 * Example of setting styles mapping:
 * $options['style_map'] = array(
 *       'main'       => 'color: black',
 *       'table'      => 'border: 1px solid black',
 *       'gutter'     => 'background-color: yellow',
 *       'brackets'   => 'color: blue',
 *       'builtin'    => 'color: red',
 *       'code'       => 'color: green',
 *       'comment'    => 'color: orange',
 *       // ....
 * );
 *
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */

class Text_Highlighter_Renderer_Html extends Text_Highlighter_Renderer_Array
{

    /**#@+
     * @access private
     */

    /**
     * Line numbering style
     *
     * @var integer
     */
    var $_numbers = 0;

    /**
     * For numberered lines - where to start
     *
     * @var integer
     */
    var $_numbers_start = 0;

    /**
     * Tab size
     *
     * @var integer
     */
    var $_tabsize = 4;

    /**
     * Highlighted code
     *
     * @var string
     */
    var $_output = '';

    /**
     * Mapping of keywords to formatting rules using inline styles
     *
     * @var array
     */
    var $_style_map = array();

    /**
     * Mapping of keywords to formatting rules using class names
     *
     * @var array
     */
    var $_class_map = array(
        'main'       => 'hl-main',
        'table'      => 'hl-table',
        'gutter'     => 'hl-gutter',
        'brackets'   => 'hl-brackets',
        'builtin'    => 'hl-builtin',
        'code'       => 'hl-code',
        'comment'    => 'hl-comment',
        'default'    => 'hl-default',
        'identifier' => 'hl-identifier',
        'inlinedoc'  => 'hl-inlinedoc',
        'inlinetags' => 'hl-inlinetags',
        'mlcomment'  => 'hl-mlcomment',
        'number'     => 'hl-number',
        'quotes'     => 'hl-quotes',
        'reserved'   => 'hl-reserved',
        'special'    => 'hl-special',
        'string'     => 'hl-string',
        'url'        => 'hl-url',
        'var'        => 'hl-var',
    );

    /**
     * Setup for links to online documentation
     *
     * This is an array with keys:
     * - url, ex. http://php.net/%s
     * - target, ex. _blank, default - no target
     * - elements, default is <code>array('reserved', 'identifier')</code>
     *
     * @var array
     */
    var $_doclinks = array();

    /**#@-*/

    /**
     * Resets renderer state
     *
     * @access protected
     *
     *
     * Descendents of Text_Highlighter call this method from the constructor,
     * passing $options they get as parameter.
     */
    function reset()
    {
        $this->_output = '';
        if (isset($this->_options['numbers'])) {
            $this->_numbers = (int)$this->_options['numbers'];
            if ($this->_numbers != HL_NUMBERS_LI
             && $this->_numbers != HL_NUMBERS_UL
             && $this->_numbers != HL_NUMBERS_OL
             && $this->_numbers != HL_NUMBERS_TABLE
             ) {
                $this->_numbers = 0;
            }
        }
        if (isset($this->_options['tabsize'])) {
            $this->_tabsize = $this->_options['tabsize'];
        }
        if (isset($this->_options['numbers_start'])) {
            $this->_numbers_start = intval($this->_options['numbers_start']);
        }
        if (isset($this->_options['doclinks']) &&
            is_array($this->_options['doclinks']) &&
            !empty($this->_options['doclinks']['url'])
        ) {

            $this->_doclinks = $this->_options['doclinks']; // keys: url, target, elements array

            if (empty($this->_options['doclinks']['elements'])) {
                $this->_doclinks['elements'] = array('reserved', 'identifier');
            }
        }
        if (isset($this->_options['style_map'])) {
            $this->_style_map = $this->_options['style_map'];
        }
        if (isset($this->_options['class_map'])) {
            $this->_class_map = array_merge($this->_class_map, $this->_options['class_map']);
        }
        $this->_htmlspecialchars = true;

    }


    /**
     * Given a CSS class name, returns the class name
     * with language name prepended, if necessary
     *
     * @access private
     *
     * @param  string $class   Token class
     */
    function _getFullClassName($class)
    {
        if (!empty($this->_options['use_language'])) {
            $the_class = $this->_language . '-' . $class;
        } else {
            $the_class = $class;
        }
        return $the_class;
    }

    /**
     * Signals that no more tokens are available
     *
     * @access public
     */
    function finalize()
    {

        // get parent's output
        parent::finalize();
        $output = parent::getOutput();
        if(empty($output))
        	return;

        $html_output = '';

        $numbers_li = false;

        if (
            $this->_numbers == HL_NUMBERS_LI ||
            $this->_numbers == HL_NUMBERS_UL ||
            $this->_numbers == HL_NUMBERS_OL
           )
        {
            $numbers_li = true;
        }

        // loop through each class=>content pair
        foreach ($output AS $token) {

            if ($this->_enumerated) {
                $key = false;
                $the_class = $token[0];
                $content = $token[1];
            } else {
                $key = key($token);
                $the_class = $key;
                $content = $token[$key];
            }

            $span = $this->_getStyling($the_class);
            $decorated_output = $this->_decorate($content, $key);
			//print "<pre> token = ".var_export($token, true)." -- span = " . htmlentities($span). "-- deco = ".$decorated_output."</pre>\n";
			$html_output .= sprintf($span, $decorated_output);
        }

        // format lists
        if (!empty($this->_numbers) && $numbers_li == true) {

            //$html_output = "<pre>".$html_output."</pre>";
            // additional whitespace for browsers that do not display
            // empty list items correctly
            $this->_output = '<li><pre>&nbsp;' . str_replace("\n", "</pre></li>\n<li><pre>&nbsp;", $html_output) . '</pre></li>';


            $start = '';
            if ($this->_numbers == HL_NUMBERS_OL && intval($this->_numbers_start) > 0)  {
                $start = ' start="' . $this->_numbers_start . '"';
            }

            $list_tag = 'ol';
            if ($this->_numbers == HL_NUMBERS_UL)  {
                $list_tag = 'ul';
            }


            $this->_output = '<' . $list_tag . $start
                             . ' ' . $this->_getStyling('main', false) . '>'
                             . $this->_output . '</'. $list_tag .'>';

        // render a table
        } else if ($this->_numbers == HL_NUMBERS_TABLE) {


            $start_number = 0;
            if (intval($this->_numbers_start)) {
                $start_number = $this->_numbers_start - 1;
            }

            $numbers = '';

            $nlines = substr_count($html_output,"\n")+1;
            for ($i=1; $i <= $nlines; $i++) {
                $numbers .= ($start_number + $i) . "\n";
            }
            $this->_output = '<table ' . $this->_getStyling('table', false) . ' width="100%"><tr>' .
                             '<td '. $this->_getStyling('gutter', false) .' align="right" valign="top">' .
                             '<pre>' . $numbers . '</pre></td><td '. $this->_getStyling('main', false) .
                             ' valign="top"><pre>' .
                             $html_output . '</pre></td></tr></table>';
        }
        if (!$this->_numbers) {
            $this->_output = '<pre>' . $html_output . '</pre>';
        }
        $this->_output = '<div ' . $this->_getStyling('main', false) . '>' . $this->_output . '</div>';
    }


    /**
     * Provides additional formatting to a keyword
     *
     * @param string $content Keyword
     * @return string Keyword with additional formatting
     * @access public
     *
     */
    function _decorate($content, $key = false)
    {
        // links to online documentation
        if (!empty($this->_doclinks) &&
            !empty($this->_doclinks['url']) &&
            in_array($key, $this->_doclinks['elements'])
        ) {

            $link = '<a href="'. sprintf($this->_doclinks['url'], $content) . '"';
            if (!empty($this->_doclinks['target'])) {
                $link.= ' target="' . $this->_doclinks['target'] . '"';
            }
            $link .= '>';
            $link.= $content;
            $link.= '</a>';

            $content = $link;

        }

        return $content;
    }

    /**
     * Returns <code>class</code> and/or <code>style</code> attribute,
     * optionally enclosed in a <code>span</code> tag
     *
     * @param string $class Class name
     * @paran boolean $span_tag Whether or not to return styling attributes in a <code>&gt;span&lt;</code> tag
     * @return string <code>span</code> tag or just a <code>class</code> and/or <code>style</code> attributes
     * @access private
     */
    function _getStyling($class, $span_tag = true)
    {
        $attrib = '';
        if (!empty($this->_style_map) &&
            !empty($this->_style_map[$class])
        ) {
            $attrib = 'style="'. $this->_style_map[$class] .'"';
        }
        if (!empty($this->_class_map) &&
            !empty($this->_class_map[$class])
        ) {
            if ($attrib) {
                $attrib .= ' ';
            }
            $attrib .= 'class="'. $this->_getFullClassName($this->_class_map[$class]) .'"';
        }

        if ($span_tag) {
            $span = '<span ' . $attrib . '>%s</span>';
            return $span;
        } else {
            return $attrib;
        }

    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>