<?php
/**
 * Auto-generated class. PYTHON syntax highlighting 
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
 * @version    generated from: : python.xml,v 1.1 2007/06/03 02:35:28 ssttoo Exp 
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */

/**
 * Auto-generated class. PYTHON syntax highlighting
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_PYTHON extends Text_Highlighter
{
    var $_language = 'python';

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
            -1 => '/((?i)\'\'\')|((?i)""")|((?i)")|((?i)\')|((?i)\\()|((?i)\\[)|((?i)[a-z_]\\w*(?=\\s*\\())|((?i)[a-z_]\\w*)|((?i)((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+))|((?i)((\\d*\\.\\d+)|(\\d+\\.\\d*)|(\\d+))j)|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\d+l?|\\b0l?\\b)|((?i)0[xX][\\da-f]+l?)|((?i)0[0-7]+l?)|((?i)#.+)/',
            0 => '/((?i)\\\\.)/',
            1 => '/((?i)\\\\.)/',
            2 => '/((?i)\\\\.)/',
            3 => '/((?i)\\\\.)/',
            4 => '/((?i)\'\'\')|((?i)""")|((?i)")|((?i)\')|((?i)\\()|((?i)\\[)|((?i)[a-z_]\\w*(?=\\s*\\())|((?i)[a-z_]\\w*)|((?i)((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+))|((?i)((\\d*\\.\\d+)|(\\d+\\.\\d*)|(\\d+))j)|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\d+l?|\\b0l?\\b)|((?i)0[xX][\\da-f]+l?)|((?i)0[0-7]+l?)|((?i)#.+)/',
            5 => '/((?i)\'\'\')|((?i)""")|((?i)")|((?i)\')|((?i)\\()|((?i)\\[)|((?i)[a-z_]\\w*(?=\\s*\\())|((?i)[a-z_]\\w*)|((?i)((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+))|((?i)((\\d*\\.\\d+)|(\\d+\\.\\d*)|(\\d+))j)|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\d+l?|\\b0l?\\b)|((?i)0[xX][\\da-f]+l?)|((?i)0[0-7]+l?)|((?i)#.+)/',
        );
        $this->_counts = array (
            -1 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 5,
                9 => 4,
                10 => 2,
                11 => 0,
                12 => 0,
                13 => 0,
                14 => 0,
            ),
            0 => 
            array (
                0 => 0,
            ),
            1 => 
            array (
                0 => 0,
            ),
            2 => 
            array (
                0 => 0,
            ),
            3 => 
            array (
                0 => 0,
            ),
            4 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 5,
                9 => 4,
                10 => 2,
                11 => 0,
                12 => 0,
                13 => 0,
                14 => 0,
            ),
            5 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 5,
                9 => 4,
                10 => 2,
                11 => 0,
                12 => 0,
                13 => 0,
                14 => 0,
            ),
        );
        $this->_delim = array (
            -1 => 
            array (
                0 => 'quotes',
                1 => 'quotes',
                2 => 'quotes',
                3 => 'quotes',
                4 => 'brackets',
                5 => 'brackets',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
                10 => '',
                11 => '',
                12 => '',
                13 => '',
                14 => '',
            ),
            0 => 
            array (
                0 => '',
            ),
            1 => 
            array (
                0 => '',
            ),
            2 => 
            array (
                0 => '',
            ),
            3 => 
            array (
                0 => '',
            ),
            4 => 
            array (
                0 => 'quotes',
                1 => 'quotes',
                2 => 'quotes',
                3 => 'quotes',
                4 => 'brackets',
                5 => 'brackets',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
                10 => '',
                11 => '',
                12 => '',
                13 => '',
                14 => '',
            ),
            5 => 
            array (
                0 => 'quotes',
                1 => 'quotes',
                2 => 'quotes',
                3 => 'quotes',
                4 => 'brackets',
                5 => 'brackets',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
                10 => '',
                11 => '',
                12 => '',
                13 => '',
                14 => '',
            ),
        );
        $this->_inner = array (
            -1 => 
            array (
                0 => 'string',
                1 => 'string',
                2 => 'string',
                3 => 'string',
                4 => 'code',
                5 => 'code',
                6 => 'identifier',
                7 => 'identifier',
                8 => 'number',
                9 => 'number',
                10 => 'number',
                11 => 'number',
                12 => 'number',
                13 => 'number',
                14 => 'comment',
            ),
            0 => 
            array (
                0 => 'special',
            ),
            1 => 
            array (
                0 => 'special',
            ),
            2 => 
            array (
                0 => 'special',
            ),
            3 => 
            array (
                0 => 'special',
            ),
            4 => 
            array (
                0 => 'string',
                1 => 'string',
                2 => 'string',
                3 => 'string',
                4 => 'code',
                5 => 'code',
                6 => 'identifier',
                7 => 'identifier',
                8 => 'number',
                9 => 'number',
                10 => 'number',
                11 => 'number',
                12 => 'number',
                13 => 'number',
                14 => 'comment',
            ),
            5 => 
            array (
                0 => 'string',
                1 => 'string',
                2 => 'string',
                3 => 'string',
                4 => 'code',
                5 => 'code',
                6 => 'identifier',
                7 => 'identifier',
                8 => 'number',
                9 => 'number',
                10 => 'number',
                11 => 'number',
                12 => 'number',
                13 => 'number',
                14 => 'comment',
            ),
        );
        $this->_end = array (
            0 => '/(?i)\'\'\'/',
            1 => '/(?i)"""/',
            2 => '/(?i)"/',
            3 => '/(?i)\'/',
            4 => '/(?i)\\)/',
            5 => '/(?i)\\]/',
        );
        $this->_states = array (
            -1 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => -1,
                10 => -1,
                11 => -1,
                12 => -1,
                13 => -1,
                14 => -1,
            ),
            0 => 
            array (
                0 => -1,
            ),
            1 => 
            array (
                0 => -1,
            ),
            2 => 
            array (
                0 => -1,
            ),
            3 => 
            array (
                0 => -1,
            ),
            4 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => -1,
                10 => -1,
                11 => -1,
                12 => -1,
                13 => -1,
                14 => -1,
            ),
            5 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => -1,
                10 => -1,
                11 => -1,
                12 => -1,
                13 => -1,
                14 => -1,
            ),
        );
        $this->_keywords = array (
            -1 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => -1,
                5 => -1,
                6 => 
                array (
                    'builtin' => '/^(__import__|abs|apply|basestring|bool|buffer|callable|chr|classmethod|cmp|coerce|compile|complex|delattr|dict|dir|divmod|enumerate|eval|execfile|file|filter|float|getattr|globals|hasattr|hash|help|hex|id|input|int|intern|isinstance|issubclass|iter|len|list|locals|long|map|max|min|object|oct|open|ord|pow|property|range|raw_input|reduce|reload|repr|round|setattr|slice|staticmethod|sum|super|str|tuple|type|unichr|unicode|vars|xrange|zip)$/',
                ),
                7 => 
                array (
                    'reserved' => '/^(and|del|for|is|raise|assert|elif|from|lambda|return|break|else|global|not|try|class|except|if|or|while|continue|exec|import|pass|yield|def|finally|in|print|False|True|None|NotImplemented|Ellipsis|Exception|SystemExit|StopIteration|StandardError|KeyboardInterrupt|ImportError|EnvironmentError|IOError|OSError|WindowsError|EOFError|RuntimeError|NotImplementedError|NameError|UnboundLocalError|AttributeError|SyntaxError|IndentationError|TabError|TypeError|AssertionError|LookupError|IndexError|KeyError|ArithmeticError|OverflowError|ZeroDivisionError|FloatingPointError|ValueError|UnicodeError|UnicodeEncodeError|UnicodeDecodeError|UnicodeTranslateError|ReferenceError|SystemError|MemoryError|Warning|UserWarning|DeprecationWarning|PendingDeprecationWarning|SyntaxWarning|OverflowWarning|RuntimeWarning|FutureWarning)$/',
                ),
                8 => 
                array (
                ),
                9 => 
                array (
                ),
                10 => 
                array (
                ),
                11 => 
                array (
                ),
                12 => 
                array (
                ),
                13 => 
                array (
                ),
                14 => 
                array (
                ),
            ),
            0 => 
            array (
                0 => 
                array (
                ),
            ),
            1 => 
            array (
                0 => 
                array (
                ),
            ),
            2 => 
            array (
                0 => 
                array (
                ),
            ),
            3 => 
            array (
                0 => 
                array (
                ),
            ),
            4 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => -1,
                5 => -1,
                6 => 
                array (
                    'builtin' => '/^(__import__|abs|apply|basestring|bool|buffer|callable|chr|classmethod|cmp|coerce|compile|complex|delattr|dict|dir|divmod|enumerate|eval|execfile|file|filter|float|getattr|globals|hasattr|hash|help|hex|id|input|int|intern|isinstance|issubclass|iter|len|list|locals|long|map|max|min|object|oct|open|ord|pow|property|range|raw_input|reduce|reload|repr|round|setattr|slice|staticmethod|sum|super|str|tuple|type|unichr|unicode|vars|xrange|zip)$/',
                ),
                7 => 
                array (
                    'reserved' => '/^(and|del|for|is|raise|assert|elif|from|lambda|return|break|else|global|not|try|class|except|if|or|while|continue|exec|import|pass|yield|def|finally|in|print|False|True|None|NotImplemented|Ellipsis|Exception|SystemExit|StopIteration|StandardError|KeyboardInterrupt|ImportError|EnvironmentError|IOError|OSError|WindowsError|EOFError|RuntimeError|NotImplementedError|NameError|UnboundLocalError|AttributeError|SyntaxError|IndentationError|TabError|TypeError|AssertionError|LookupError|IndexError|KeyError|ArithmeticError|OverflowError|ZeroDivisionError|FloatingPointError|ValueError|UnicodeError|UnicodeEncodeError|UnicodeDecodeError|UnicodeTranslateError|ReferenceError|SystemError|MemoryError|Warning|UserWarning|DeprecationWarning|PendingDeprecationWarning|SyntaxWarning|OverflowWarning|RuntimeWarning|FutureWarning)$/',
                ),
                8 => 
                array (
                ),
                9 => 
                array (
                ),
                10 => 
                array (
                ),
                11 => 
                array (
                ),
                12 => 
                array (
                ),
                13 => 
                array (
                ),
                14 => 
                array (
                ),
            ),
            5 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => -1,
                5 => -1,
                6 => 
                array (
                    'builtin' => '/^(__import__|abs|apply|basestring|bool|buffer|callable|chr|classmethod|cmp|coerce|compile|complex|delattr|dict|dir|divmod|enumerate|eval|execfile|file|filter|float|getattr|globals|hasattr|hash|help|hex|id|input|int|intern|isinstance|issubclass|iter|len|list|locals|long|map|max|min|object|oct|open|ord|pow|property|range|raw_input|reduce|reload|repr|round|setattr|slice|staticmethod|sum|super|str|tuple|type|unichr|unicode|vars|xrange|zip)$/',
                ),
                7 => 
                array (
                    'reserved' => '/^(and|del|for|is|raise|assert|elif|from|lambda|return|break|else|global|not|try|class|except|if|or|while|continue|exec|import|pass|yield|def|finally|in|print|False|True|None|NotImplemented|Ellipsis|Exception|SystemExit|StopIteration|StandardError|KeyboardInterrupt|ImportError|EnvironmentError|IOError|OSError|WindowsError|EOFError|RuntimeError|NotImplementedError|NameError|UnboundLocalError|AttributeError|SyntaxError|IndentationError|TabError|TypeError|AssertionError|LookupError|IndexError|KeyError|ArithmeticError|OverflowError|ZeroDivisionError|FloatingPointError|ValueError|UnicodeError|UnicodeEncodeError|UnicodeDecodeError|UnicodeTranslateError|ReferenceError|SystemError|MemoryError|Warning|UserWarning|DeprecationWarning|PendingDeprecationWarning|SyntaxWarning|OverflowWarning|RuntimeWarning|FutureWarning)$/',
                ),
                8 => 
                array (
                ),
                9 => 
                array (
                ),
                10 => 
                array (
                ),
                11 => 
                array (
                ),
                12 => 
                array (
                ),
                13 => 
                array (
                ),
                14 => 
                array (
                ),
            ),
        );
        $this->_parts = array (
            0 => 
            array (
                0 => NULL,
            ),
            1 => 
            array (
                0 => NULL,
            ),
            2 => 
            array (
                0 => NULL,
            ),
            3 => 
            array (
                0 => NULL,
            ),
            4 => 
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
                9 => NULL,
                10 => NULL,
                11 => NULL,
                12 => NULL,
                13 => NULL,
                14 => NULL,
            ),
            5 => 
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
                9 => NULL,
                10 => NULL,
                11 => NULL,
                12 => NULL,
                13 => NULL,
                14 => NULL,
            ),
        );
        $this->_subst = array (
            -1 => 
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
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
                14 => false,
            ),
            0 => 
            array (
                0 => false,
            ),
            1 => 
            array (
                0 => false,
            ),
            2 => 
            array (
                0 => false,
            ),
            3 => 
            array (
                0 => false,
            ),
            4 => 
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
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
                14 => false,
            ),
            5 => 
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
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
                14 => false,
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
            'builtin' => 'builtin',
            'reserved' => 'reserved',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }
    
}