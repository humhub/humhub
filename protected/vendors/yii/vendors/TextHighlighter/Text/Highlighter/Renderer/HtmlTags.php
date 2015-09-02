<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * HTML renderer that uses only basic html tags
 *
 * PHP versions 4 and 5. Based on the "normal" HTML renderer by Andrey Demenev.
 * It's designed to work with user agents that support only a limited number of
 * HTML tags. Like the iPod which supports only b, i, u and a.
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
 * @copyright  2005 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    CVS: $Id: HtmlTags.php,v 1.1 2007/06/03 02:37:09 ssttoo Exp $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

/**
 * @ignore
 */

require_once dirname(__FILE__).'/../Renderer.php';
require_once dirname(__FILE__).'/../Renderer/Array.php';

/**
 * HTML basic tags renderer, based on Andrey Demenev's HTML renderer.
 *
 * Elements of $options argument of constructor (each being optional):
 *
 * - 'numbers' - Line numbering TRUE or FALSE. Default is FALSE.
 * - 'tabsize' - Tab size, default is 4.
 * - 'tags'    - Array, containing the tags to be used for highlighting
 *
 * Here's the listing of the default tags:
 * - 'default'    => '',
 * - 'code'       => '',
 * - 'brackets'   => 'b',
 * - 'comment'    => 'i',
 * - 'mlcomment'  => 'i',
 * - 'quotes'     => '',
 * - 'string'     => 'i',
 * - 'identifier' => 'b',
 * - 'builtin'    => 'b',
 * - 'reserved'   => 'u',
 * - 'inlinedoc'  => 'i',
 * - 'var'        => 'b',
 * - 'url'        => 'i',
 * - 'special'    => '',
 * - 'number'     => '',
 * - 'inlinetags' => ''
 *
 * @author Stoyan Stefanov <ssttoo@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2005 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.5.0
 * @link       http://pear.php.net/package/Text_Highlighter
 */

class Text_Highlighter_Renderer_HtmlTags extends Text_Highlighter_Renderer_Array
{

    /**#@+
     * @access private
     */

    /**
     * Line numbering - will use 'ol' tag
     *
     * @var boolean
     */
    var $_numbers = false;

    /**
     * HTML tags map
     *
     * @var array
     */
    var $_hilite_tags = array(
        'default'    => '',
        'code'       => '',
        'brackets'   => 'b',
        'comment'    => 'i',
        'mlcomment'  => 'i',
        'quotes'     => '',
        'string'     => 'i',
        'identifier' => 'b',
        'builtin'    => 'b',
        'reserved'   => 'u',
        'inlinedoc'  => 'i',
        'var'        => 'b',
        'url'        => 'i',
        'special'    => '',
        'number'     => '',
        'inlinetags' => '',
    );

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
        parent::reset();
        if (isset($this->_options['numbers'])) {
            $this->_numbers = $this->_options['numbers'];
        }
        if (isset($this->_options['tags'])) {
            $this->_hilite_tags = array_merge($this->_tags, $this->_options['tags']);
        }
    }


    /**
     * Signals that no more tokens are available
     *
     * @abstract
     * @access public
     *
     */
    function finalize()
    {

        // get parent's output
        parent::finalize();
        $output = parent::getOutput();

        $html_output = '';

        // loop through each class=>content pair
        foreach ($output AS $token) {

            if ($this->_enumerated) {
                $class = $token[0];
                $content = $token[1];
            } else {
                $key = key($token);
                $class = $key;
                $content = $token[$key];
            }

            $iswhitespace = ctype_space($content);
            if (!$iswhitespace && !empty($this->_hilite_tags[$class])) {
                $html_output .= '<'. $this->_hilite_tags[$class] . '>' . $content . '</'. $this->_hilite_tags[$class] . '>';
            } else {
                $html_output .= $content;
            }
        }


        if ($this->_numbers) {
            /* additional whitespace for browsers that do not display
            empty list items correctly */
            $html_output = '<li>&nbsp;' . str_replace("\n", "</li>\n<li>&nbsp;", $html_output) . '</li>';
            $this->_output = '<ol>' . str_replace(' ', '&nbsp;', $html_output) . '</ol>';
        } else {
            $this->_output = '<pre>' . $html_output . '</pre>';
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