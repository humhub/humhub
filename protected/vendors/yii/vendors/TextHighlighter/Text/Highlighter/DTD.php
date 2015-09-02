<?php
/**
 * Auto-generated class. DTD syntax highlighting 
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
 * @version    generated from: : dtd.xml,v 1.1 2007/06/03 02:35:28 ssttoo Exp 
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */

/**
 * Auto-generated class. DTD syntax highlighting
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_DTD extends Text_Highlighter
{
    var $_language = 'dtd';

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
            -1 => '/(\\<!--)|(\\<\\!\\[)|((\\&|\\%)[\\w\\-\\.]+;)/',
            0 => '//',
            1 => '/(\\<!--)|(\\<)|(#PCDATA\\b)|((\\&|\\%)[\\w\\-\\.]+;)|((?i)[a-z][a-z\\d\\-\\,:]+)/',
            2 => '/(\\<!--)|(\\()|(\')|(")|((?<=\\<)!(ENTITY|ATTLIST|ELEMENT|NOTATION)\\b)|(\\s(#(IMPLIED|REQUIRED|FIXED))|CDATA|ENTITY|NOTATION|NMTOKENS?|PUBLIC|SYSTEM\\b)|(#PCDATA\\b)|((\\&|\\%)[\\w\\-\\.]+;)|((?i)[a-z][a-z\\d\\-\\,:]+)/',
            3 => '/(\\()|((\\&|\\%)[\\w\\-\\.]+;)|((?i)[a-z][a-z\\d\\-\\,:]+)/',
            4 => '/((\\&|\\%)[\\w\\-\\.]+;)/',
            5 => '/((\\&|\\%)[\\w\\-\\.]+;)/',
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
                3 => 1,
                4 => 0,
            ),
            2 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 1,
                5 => 2,
                6 => 0,
                7 => 1,
                8 => 0,
            ),
            3 => 
            array (
                0 => 0,
                1 => 1,
                2 => 0,
            ),
            4 => 
            array (
                0 => 1,
            ),
            5 => 
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
                0 => 'comment',
                1 => 'brackets',
                2 => '',
                3 => '',
                4 => '',
            ),
            2 => 
            array (
                0 => 'comment',
                1 => 'brackets',
                2 => 'quotes',
                3 => 'quotes',
                4 => '',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
            ),
            3 => 
            array (
                0 => 'brackets',
                1 => '',
                2 => '',
            ),
            4 => 
            array (
                0 => '',
            ),
            5 => 
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
                0 => 'comment',
                1 => 'code',
                2 => 'reserved',
                3 => 'special',
                4 => 'identifier',
            ),
            2 => 
            array (
                0 => 'comment',
                1 => 'code',
                2 => 'string',
                3 => 'string',
                4 => 'var',
                5 => 'reserved',
                6 => 'reserved',
                7 => 'special',
                8 => 'identifier',
            ),
            3 => 
            array (
                0 => 'code',
                1 => 'special',
                2 => 'identifier',
            ),
            4 => 
            array (
                0 => 'special',
            ),
            5 => 
            array (
                0 => 'special',
            ),
        );
        $this->_end = array (
            0 => '/--\\>/',
            1 => '/\\]\\]\\>/',
            2 => '/\\>/',
            3 => '/\\)/',
            4 => '/\'/',
            5 => '/"/',
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
                0 => 0,
                1 => 2,
                2 => -1,
                3 => -1,
                4 => -1,
            ),
            2 => 
            array (
                0 => 0,
                1 => 3,
                2 => 4,
                3 => 5,
                4 => -1,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
            ),
            3 => 
            array (
                0 => 3,
                1 => -1,
                2 => -1,
            ),
            4 => 
            array (
                0 => -1,
            ),
            5 => 
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
                0 => -1,
                1 => -1,
                2 => 
                array (
                ),
                3 => 
                array (
                ),
                4 => 
                array (
                ),
            ),
            2 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => 
                array (
                ),
                5 => 
                array (
                ),
                6 => 
                array (
                ),
                7 => 
                array (
                ),
                8 => 
                array (
                ),
            ),
            3 => 
            array (
                0 => -1,
                1 => 
                array (
                ),
                2 => 
                array (
                ),
            ),
            4 => 
            array (
                0 => 
                array (
                ),
            ),
            5 => 
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
                3 => NULL,
                4 => NULL,
            ),
            2 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => NULL,
                6 => NULL,
                7 => NULL,
                8 => NULL,
            ),
            3 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
            ),
            4 => 
            array (
                0 => NULL,
            ),
            5 => 
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
                3 => false,
                4 => false,
            ),
            2 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
                7 => false,
                8 => false,
            ),
            3 => 
            array (
                0 => false,
                1 => false,
                2 => false,
            ),
            4 => 
            array (
                0 => false,
            ),
            5 => 
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