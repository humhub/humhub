<?php
/**
 * Auto-generated class. CSS syntax highlighting
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
 * @version    generated from: : css.xml,v 1.2 2008/01/01 23:45:07 ssttoo Exp
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */

/**
 * Auto-generated class. CSS syntax highlighting
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_CSS extends Text_Highlighter
{
    var $_language = 'css';

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
            -1 => '/((?i)\\/\\*)|((?i)(@[a-z\\d]+))|((?i)(((\\.|#)?[a-z]+[a-z\\d\\-]*(?![a-z\\d\\-]))|(\\*))(?!\\s*:\\s*[\\s\\{]))|((?i):[a-z][a-z\\d\\-]*)|((?i)\\[)|((?i)\\{)/',
            0 => '//',
            1 => '/((?i)\\d*\\.?\\d+(\\%|em|ex|pc|pt|px|in|mm|cm))|((?i)\\d*\\.?\\d+)|((?i)[a-z][a-z\\d\\-]*)|((?i)#([\\da-f]{6}|[\\da-f]{3})\\b)/',
            2 => '/((?i)\')|((?i)")|((?i)[\\w\\-\\:]+)/',
            3 => '/((?i)\\/\\*)|((?i)[a-z][a-z\\d\\-]*\\s*:)|((?i)(((\\.|#)?[a-z]+[a-z\\d\\-]*(?![a-z\\d\\-]))|(\\*))(?!\\s*:\\s*[\\s\\{]))|((?i)\\{)/',
            4 => '/((?i)\\\\[\\\\(\\\\)\\\\])/',
            5 => '/((?i)\\\\\\\\|\\\\"|\\\\\'|\\\\`)/',
            6 => '/((?i)\\\\\\\\|\\\\"|\\\\\'|\\\\`|\\\\t|\\\\n|\\\\r)/',
        );
        $this->_counts = array (
            -1 =>
            array (
                0 => 0,
                1 => 1,
                2 => 4,
                3 => 0,
                4 => 0,
                5 => 0,
            ),
            0 =>
            array (
            ),
            1 =>
            array (
                0 => 1,
                1 => 0,
                2 => 0,
                3 => 1,
            ),
            2 =>
            array (
                0 => 0,
                1 => 0,
                2 => 0,
            ),
            3 =>
            array (
                0 => 0,
                1 => 0,
                2 => 4,
                3 => 0,
            ),
            4 =>
            array (
                0 => 0,
            ),
            5 =>
            array (
                0 => 0,
            ),
            6 =>
            array (
                0 => 0,
            ),
        );
        $this->_delim = array (
            -1 =>
            array (
                0 => 'comment',
                1 => '',
                2 => '',
                3 => '',
                4 => 'brackets',
                5 => 'brackets',
            ),
            0 =>
            array (
            ),
            1 =>
            array (
                0 => '',
                1 => '',
                2 => '',
                3 => '',
            ),
            2 =>
            array (
                0 => 'quotes',
                1 => 'quotes',
                2 => '',
            ),
            3 =>
            array (
                0 => 'comment',
                1 => 'reserved',
                2 => '',
                3 => 'brackets',
            ),
            4 =>
            array (
                0 => '',
            ),
            5 =>
            array (
                0 => '',
            ),
            6 =>
            array (
                0 => '',
            ),
        );
        $this->_inner = array (
            -1 =>
            array (
                0 => 'comment',
                1 => 'var',
                2 => 'identifier',
                3 => 'special',
                4 => 'code',
                5 => 'code',
            ),
            0 =>
            array (
            ),
            1 =>
            array (
                0 => 'number',
                1 => 'number',
                2 => 'code',
                3 => 'var',
            ),
            2 =>
            array (
                0 => 'string',
                1 => 'string',
                2 => 'var',
            ),
            3 =>
            array (
                0 => 'comment',
                1 => 'code',
                2 => 'identifier',
                3 => 'code',
            ),
            4 =>
            array (
                0 => 'string',
            ),
            5 =>
            array (
                0 => 'special',
            ),
            6 =>
            array (
                0 => 'special',
            ),
        );
        $this->_end = array (
            0 => '/(?i)\\*\\//',
            1 => '/(?i)(?=;|\\})/',
            2 => '/(?i)\\]/',
            3 => '/(?i)\\}/',
            4 => '/(?i)\\)/',
            5 => '/(?i)\'/',
            6 => '/(?i)"/',
        );
        $this->_states = array (
            -1 =>
            array (
                0 => 0,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => 2,
                5 => 3,
            ),
            0 =>
            array (
            ),
            1 =>
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
            ),
            2 =>
            array (
                0 => 5,
                1 => 6,
                2 => -1,
            ),
            3 =>
            array (
                0 => 0,
                1 => 1,
                2 => -1,
                3 => 3,
            ),
            4 =>
            array (
                0 => -1,
            ),
            5 =>
            array (
                0 => -1,
            ),
            6 =>
            array (
                0 => -1,
            ),
        );
        $this->_keywords = array (
            -1 =>
            array (
                0 => -1,
                1 =>
                array (
                ),
                2 =>
                array (
                ),
                3 =>
                array (
                ),
                4 => -1,
                5 => -1,
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
                2 =>
                array (
                    'propertyValue' => '/^((?i)far-left|left|center-left|center-right|center|far-right|right-side|right|behind|leftwards|rightwards|inherit|scroll|fixed|transparent|none|repeat-x|repeat-y|repeat|no-repeat|collapse|separate|auto|top|bottom|both|open-quote|close-quote|no-open-quote|no-close-quote|crosshair|default|pointer|move|e-resize|ne-resize|nw-resize|n-resize|se-resize|sw-resize|s-resize|text|wait|help|ltr|rtl|inline|block|list-item|run-in|compact|marker|table|inline-table|table-row-group|table-header-group|table-footer-group|table-row|table-column-group|table-column|table-cell|table-caption|below|level|above|higher|lower|show|hide|caption|icon|menu|message-box|small-caption|status-bar|normal|wider|narrower|ultra-condensed|extra-condensed|condensed|semi-condensed|semi-expanded|expanded|extra-expanded|ultra-expanded|italic|oblique|small-caps|bold|bolder|lighter|inside|outside|disc|circle|square|decimal|decimal-leading-zero|lower-roman|upper-roman|lower-greek|lower-alpha|lower-latin|upper-alpha|upper-latin|hebrew|armenian|georgian|cjk-ideographic|hiragana|katakana|hiragana-iroha|katakana-iroha|crop|cross|invert|visible|hidden|always|avoid|x-low|low|medium|high|x-high|mix?|repeat?|static|relative|absolute|portrait|landscape|spell-out|once|digits|continuous|code|x-slow|slow|fast|x-fast|faster|slower|justify|underline|overline|line-through|blink|capitalize|uppercase|lowercase|embed|bidi-override|baseline|sub|super|text-top|middle|text-bottom|silent|x-soft|soft|loud|x-loud|pre|nowrap|serif|sans-serif|cursive|fantasy|monospace|empty|string|strict|loose|char|true|false|dotted|dashed|solid|double|groove|ridge|inset|outset|larger|smaller|xx-small|x-small|small|large|x-large|xx-large|all|newspaper|distribute|distribute-all-lines|distribute-center-last|inter-word|inter-ideograph|inter-cluster|kashida|ideograph-alpha|ideograph-numeric|ideograph-parenthesis|ideograph-space|keep-all|break-all|break-word|lr-tb|tb-rl|thin|thick|inline-block|w-resize|hand|distribute-letter|distribute-space|whitespace|male|female|child)$/',
                    'namedcolor' => '/^((?i)aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|purple|red|silver|teal|white|yellow|activeborder|activecaption|appworkspace|background|buttonface|buttonhighlight|buttonshadow|buttontext|captiontext|graytext|highlight|highlighttext|inactiveborder|inactivecaption|inactivecaptiontext|infobackground|infotext|menu|menutext|scrollbar|threeddarkshadow|threedface|threedhighlight|threedlightshadow|threedshadow|window|windowframe|windowtext)$/',
                ),
                3 =>
                array (
                ),
            ),
            2 =>
            array (
                0 => -1,
                1 => -1,
                2 =>
                array (
                ),
            ),
            3 =>
            array (
                0 => -1,
                1 => -1,
                2 =>
                array (
                ),
                3 => -1,
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
            6 =>
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
                0 =>
                array (
                    1 => 'string',
                ),
                1 => NULL,
                2 => NULL,
                3 => NULL,
            ),
            2 =>
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
            ),
            3 =>
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
            ),
            4 =>
            array (
                0 => NULL,
            ),
            5 =>
            array (
                0 => NULL,
            ),
            6 =>
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
                3 => false,
                4 => false,
                5 => false,
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
            ),
            2 =>
            array (
                0 => false,
                1 => false,
                2 => false,
            ),
            3 =>
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
            ),
            4 =>
            array (
                0 => false,
            ),
            5 =>
            array (
                0 => false,
            ),
            6 =>
            array (
                0 => false,
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
            'propertyValue' => 'string',
            'namedcolor' => 'var',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }

}