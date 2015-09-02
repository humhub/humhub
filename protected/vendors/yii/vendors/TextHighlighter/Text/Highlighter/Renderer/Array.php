<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Array renderer.
 *
 * Produces an array that contains class names and content pairs.
 * The array can be enumerated or associative. Associative means
 * <code>class =&gt; content</code> pairs.
 * Based on the HTML renderer by Andrey Demenev.
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package    Text_Highlighter
 * @author     Stoyan Stefanov <ssttoo@gmail.com>
 * @copyright  2006 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    CVS: $Id: Array.php,v 1.1 2007/06/03 02:37:08 ssttoo Exp $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

/**
 * @ignore
 */

require_once dirname(__FILE__).'/../Renderer.php';

/**
 * Array renderer, based on Andrey Demenev's HTML renderer.
 *
 * In addition to the options supported by the HTML renderer,
 * the following options were also introduced:
 * <ul><li>htmlspecialchars - whether or not htmlspecialchars() will
 *                            be called on the content, default TRUE</li>
 *     <li>enumerated - type of array produced, default FALSE,
 *                            meaning associative array</li>
 * </ul>
 *
 *
 * @author     Stoyan Stefanov <ssttoo@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2006 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.5.0
 * @link       http://pear.php.net/package/Text_Highlighter
 */

class Text_Highlighter_Renderer_Array extends Text_Highlighter_Renderer
{

    /**#@+
     * @access private
     */

    /**
     * Tab size
     *
     * @var integer
     */
    var $_tabsize = 4;

    /**
     * Should htmlentities() will be called
     *
     * @var boolean
     */
    var $_htmlspecialchars = true;

    /**
     * Enumerated or associative array
     *
     * @var integer
     */
    var $_enumerated = false;

    /**
     * Array containing highlighting rules
     *
     * @var array
     */
    var $_output = array();

    /**#@-*/

    /**
     * Preprocesses code
     *
     * @access public
     *
     * @param  string $str Code to preprocess
     * @return string Preprocessed code
     */
    function preprocess($str)
    {
        // normalize whitespace and tabs
        $str = str_replace("\r\n","\n", $str);
        // some browsers refuse to display empty lines
        $str = preg_replace('~^$~m'," ", $str);
        $str = str_replace("\t",str_repeat(' ', $this->_tabsize), $str);
        return rtrim($str);
    }


    /**
     * Resets renderer state
     *
     * Descendents of Text_Highlighter call this method from the constructor,
     * passing $options they get as parameter.
     *
     * @access protected
     */
    function reset()
    {
        $this->_output = array();
        $this->_lastClass = 'default';
        if (isset($this->_options['tabsize'])) {
            $this->_tabsize = $this->_options['tabsize'];
        }
        if (isset($this->_options['htmlspecialchars'])) {
            $this->_htmlspecialchars = $this->_options['htmlspecialchars'];
        }
        if (isset($this->_options['enumerated'])) {
            $this->_enumerated = $this->_options['enumerated'];
        }
    }



    /**
     * Accepts next token
     *
     * @abstract
     * @access public
     * @param  string $class   Token class
     * @param  string $content Token content
     */
    function acceptToken($class, $content)
    {


        $theClass = $this->_getFullClassName($class);
        if ($this->_htmlspecialchars) {
            $content = htmlspecialchars($content);
        }
        if ($this->_enumerated) {
            $this->_output[] = array($class, $content);
        } else {
            $this->_output[][$class] = $content;
        }
        $this->_lastClass = $class;

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
            $theClass = $this->_language . '-' . $class;
        } else {
            $theClass = $class;
        }
        return $theClass;
    }

    /**
     * Get generated output
     *
     * @abstract
     * @return array Highlighted code as an array
     * @access public
     */
    function getOutput()
    {
        return $this->_output;
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