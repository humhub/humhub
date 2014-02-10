<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * BB code renderer.
 *
 * This BB renderer produces BB code, ready to be pasted in bulletin boards and
 * other applications that accept BB code. Based on the HTML renderer by Andrey Demenev.
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
 * @version    CVS: $Id: BB.php,v 1.1 2007/06/03 02:37:08 ssttoo Exp $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

/**
 * @ignore
 */

require_once dirname(__FILE__).'/../Renderer.php';

/**
 * BB code renderer, based on Andrey Demenev's HTML renderer.
 *
 * Elements of $options argument of constructor (each being optional):
 *
 * - 'numbers' - Line numbering TRUE or FALSE
 * - 'tabsize' - Tab size, default is 4
 * - 'bb_tags' - An array containing three BB tags, see below
 * - 'tag_brackets' - An array that conains opening and closing tags, [ and ]
 * - 'colors' - An array with all the colors to be used for highlighting
 *
 * The default BB tags are:
 * - 'color' => 'color'
 * - 'list'  => 'list'
 * - 'list_item' => '*'
 *
 * The default colors for the highlighter are:
 * - 'default'    => 'Black',
 * - 'code'       => 'Gray',
 * - 'brackets'   => 'Olive',
 * - 'comment'    => 'Orange',
 * - 'mlcomment'  => 'Orange',
 * - 'quotes'     => 'Darkred',
 * - 'string'     => 'Red',
 * - 'identifier' => 'Blue',
 * - 'builtin'    => 'Teal',
 * - 'reserved'   => 'Green',
 * - 'inlinedoc'  => 'Blue',
 * - 'var'        => 'Darkblue',
 * - 'url'        => 'Blue',
 * - 'special'    => 'Navy',
 * - 'number'     => 'Maroon',
 * - 'inlinetags' => 'Blue',
 *
 *
 * @author     Stoyan Stefanov <ssttoo@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  20045 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.5.0
 * @link       http://pear.php.net/package/Text_Highlighter
 */

class Text_Highlighter_Renderer_BB extends Text_Highlighter_Renderer_Array
{

    /**#@+
     * @access private
     */

    /**
     * Line numbering - will use the specified BB tag for listings
     *
     * @var boolean
     */
    var $_numbers = false;

    /**
     * BB tags to be used
     *
     * @var array
     */
    var $_bb_tags = array (
        'color'     => 'color',
        'list'      => 'list',
        'list_item' => '*',
        'code'      => 'code',
    );

    /**
     * BB brackets - [ and ]
     *
     * @var array
     */
    var $_tag_brackets = array ('start' => '[', 'end' => ']');

    /**
     * Colors map
     *
     * @var boolean
     */
    var $_colors = array(
        'default'    => 'Black',
        'code'       => 'Gray',
        'brackets'   => 'Olive',
        'comment'    => 'Orange',
        'mlcomment'  => 'Orange',
        'quotes'     => 'Darkred',
        'string'     => 'Red',
        'identifier' => 'Blue',
        'builtin'    => 'Teal',
        'reserved'   => 'Green',
        'inlinedoc'  => 'Blue',
        'var'        => 'Darkblue',
        'url'        => 'Blue',
        'special'    => 'Navy',
        'number'     => 'Maroon',
        'inlinetags' => 'Blue',
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
        if (isset($this->_options['bb_tags'])) {
            $this->_bb_tags = array_merge($this->_bb_tags, $this->_options['bb_tags']);
        }
        if (isset($this->_options['tag_brackets'])) {
            $this->_tag_brackets = array_merge($this->_tag_brackets, $this->_options['tag_brackets']);
        }
        if (isset($this->_options['colors'])) {
            $this->_colors = array_merge($this->_colors, $this->_options['colors']);
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

        $bb_output = '';

        $color_start = $this->_tag_brackets['start'] . $this->_bb_tags['color'] . '=%s'  . $this->_tag_brackets['end'];
        $color_end   = $this->_tag_brackets['start'] . '/' . $this->_bb_tags['color'] . $this->_tag_brackets['end'];

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
            if (!$iswhitespace && !empty($this->_colors[$class])) {
                $bb_output .= sprintf($color_start, $this->_colors[$class]);
                $bb_output .= $content;
                $bb_output .= $color_end;
            } else {
                $bb_output .= $content;
            }
        }

        if ($this->_numbers) {

            $item_tag = $this->_tag_brackets['start'] .
                        $this->_bb_tags['list_item'] .
                        $this->_tag_brackets['end'];
            $this->_output = $item_tag . str_replace("\n", "\n". $item_tag .' ', $bb_output);
            $this->_output = $this->_tag_brackets['start'] .
                             $this->_bb_tags['list'] .
                             $this->_tag_brackets['end'] .
                             $this->_output .
                             $this->_tag_brackets['start'] .
                             '/'.
                             $this->_bb_tags['list'] .
                             $this->_tag_brackets['end']
                             ;
        } else {
            $this->_output = $this->_tag_brackets['start'] .
                             $this->_bb_tags['code'] .
                             $this->_tag_brackets['end'] .
                             $bb_output .
                             $this->_tag_brackets['start'] .
                             '/' .
                             $this->_bb_tags['code'] .
                             $this->_tag_brackets['end'];
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