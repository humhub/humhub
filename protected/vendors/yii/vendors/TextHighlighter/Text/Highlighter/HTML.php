<?php
/**
 * Auto-generated class. HTML syntax highlighting 
 *
 * PHP version 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @link       http://pear.php.net/package/Text_Highlighter
 * @category   Text
 * @package    Text_Highlighter
 * @version    generated from: : html.xml,v 1.1 2007/06/03 02:35:28 ssttoo Exp 
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */

/**
 * @ignore
 */

/**
 * Auto-generated class. HTML syntax highlighting
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_HTML extends Text_Highlighter
{
    var $_language = 'html';

    /**
     *  Constructor
     *
     * @param array  $options
     * @access public
     */
    function __construct($options=array())
    {

        $this->_options = $options;
        $this->_regs = array (
            -1 => '/((?i)\\<!--)|((?i)\\<[\\?\\/]?)|((?i)(&)[\\w\\-\\.]+;)/',
            0 => '//',
            1 => '/((?i)(?<=[\\<\\/?])[\\w\\-\\:]+)|((?i)[\\w\\-\\:]+)|((?i)")/',
            2 => '/((?i)(&)[\\w\\-\\.]+;)/',
        );
        $this->_counts = array (
            -1 => 
            array (
                0 => 0,
                1 => 0,
                2 => 1,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
            ),
            2 => 
            array (
                0 => 1,
            ),
        );
        $this->_delim = array (
            -1 => 
            array (
                0 => 'comment',
                1 => 'brackets',
                2 => '',
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => '',
                1 => '',
                2 => 'quotes',
            ),
            2 => 
            array (
                0 => '',
            ),
        );
        $this->_inner = array (
            -1 => 
            array (
                0 => 'comment',
                1 => 'code',
                2 => 'special',
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => 'reserved',
                1 => 'var',
                2 => 'string',
            ),
            2 => 
            array (
                0 => 'special',
            ),
        );
        $this->_end = array (
            0 => '/(?i)--\\>/',
            1 => '/(?i)[\\/\\?]?\\>/',
            2 => '/(?i)"/',
        );
        $this->_states = array (
            -1 => 
            array (
                0 => 0,
                1 => 1,
                2 => -1,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => -1,
                1 => -1,
                2 => 2,
            ),
            2 => 
            array (
                0 => -1,
            ),
        );
        $this->_keywords = array (
            -1 => 
            array (
                0 => -1,
                1 => -1,
                2 => 
                array (
                ),
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => 
                array (
                ),
                1 => 
                array (
                ),
                2 => -1,
            ),
            2 => 
            array (
                0 => 
                array (
                ),
            ),
        );
        $this->_parts = array (
            0 => 
            array (
            ),
            1 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
            ),
            2 => 
            array (
                0 => NULL,
            ),
        );
        $this->_subst = array (
            -1 => 
            array (
                0 => false,
                1 => false,
                2 => false,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => false,
                1 => false,
                2 => false,
            ),
            2 => 
            array (
                0 => false,
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }
    
}