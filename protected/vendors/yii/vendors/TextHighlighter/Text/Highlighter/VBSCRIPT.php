<?php
/**
 * Auto-generated class. VBSCRIPT syntax highlighting
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
 * @version    generated from: : vbscript.xml,v 1.2 2008/01/02 00:05:52 ssttoo Exp
 * @author Daniel Fruzynski <daniel-AT-poradnik-webmastera.com>
 *
 */

/**
 * Auto-generated class. VBSCRIPT syntax highlighting
 *
 * @author Daniel Fruzynski <daniel-AT-poradnik-webmastera.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_VBSCRIPT extends Text_Highlighter
{
    var $_language = 'vbscript';

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
            -1 => '/((?i)\\()|((?i)")|((?i)\'|[Rr][Ee][Mm]\\b)|((?i)\\d*\\.?\\d+)|((?i)&H[0-9a-fA-F]+)|((?i)[a-z_]\\w*)/',
            0 => '/((?i)\\()|((?i)")|((?i)\'|[Rr][Ee][Mm]\\b)|((?i)\\d*\\.?\\d+)|((?i)&H[0-9a-fA-F]+)|((?i)[a-z_]\\w*)/',
            1 => '//',
            2 => '/((?i)((https?|ftp):\\/\\/[\\w\\?\\.\\-\\&=\\/%+]+)|(^|[\\s,!?])www\\.\\w+\\.\\w+[\\w\\?\\.\\&=\\/%+]*)|((?i)\\w+[\\.\\w\\-]+@(\\w+[\\.\\w\\-])+)|((?i)\\b(note|fixme):)|((?i)\\$\\w+:.+\\$)/',
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
            ),
            0 =>
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
            ),
            1 =>
            array (
            ),
            2 =>
            array (
                0 => 3,
                1 => 1,
                2 => 1,
                3 => 0,
            ),
        );
        $this->_delim = array (
            -1 =>
            array (
                0 => 'brackets',
                1 => 'quotes',
                2 => 'comment',
                3 => '',
                4 => '',
                5 => '',
            ),
            0 =>
            array (
                0 => 'brackets',
                1 => 'quotes',
                2 => 'comment',
                3 => '',
                4 => '',
                5 => '',
            ),
            1 =>
            array (
            ),
            2 =>
            array (
                0 => '',
                1 => '',
                2 => '',
                3 => '',
            ),
        );
        $this->_inner = array (
            -1 =>
            array (
                0 => 'code',
                1 => 'string',
                2 => 'comment',
                3 => 'number',
                4 => 'number',
                5 => 'identifier',
            ),
            0 =>
            array (
                0 => 'code',
                1 => 'string',
                2 => 'comment',
                3 => 'number',
                4 => 'number',
                5 => 'identifier',
            ),
            1 =>
            array (
            ),
            2 =>
            array (
                0 => 'url',
                1 => 'url',
                2 => 'inlinedoc',
                3 => 'inlinedoc',
            ),
        );
        $this->_end = array (
            0 => '/(?i)\\)/',
            1 => '/(?i)"/',
            2 => '/(?mi)$/',
        );
        $this->_states = array (
            -1 =>
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => -1,
                4 => -1,
                5 => -1,
            ),
            0 =>
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => -1,
                4 => -1,
                5 => -1,
            ),
            1 =>
            array (
            ),
            2 =>
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
            ),
        );
        $this->_keywords = array (
            -1 =>
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 =>
                array (
                ),
                4 =>
                array (
                ),
                5 =>
                array (
                    'constants' => '/^((?i)vbblack|vbred|vbgreen|vbyellow|vbblue|vbmagenta|vbcyan|vbwhite|vbbinarycompare|vbtextcompare|vbsunday|vbmonday|vbtuesday|vbwednesday|vbthursday|vbfriday|vbsaturday|vbusesystemdayofweek|vbfirstjan1|vbfirstfourdays|vbfirstfullweek|vbgeneraldate|vblongdate|vbshortdate|vblongtime|vbshorttime|vbobjecterror|vbokonly|vbokcancel|vbabortretryignore|vbyesnocancel|vbyesno|vbretrycancel|vbcritical|vbquestion|vbexclamation|vbinformation|vbdefaultbutton1|vbdefaultbutton2|vbdefaultbutton3|vbdefaultbutton4|vbapplicationmodal|vbsystemmodal|vbok|vbcancel|vbabort|vbretry|vbignore|vbyes|vbno|vbcr|vbcrlf|vbformfeed|vblf|vbnewline|vbnullchar|vbnullstring|vbtab|vbverticaltab|vbusedefault|vbtrue|vbfalse|vbempty|vbnull|vbinteger|vblong|vbsingle|vbdouble|vbcurrency|vbdate|vbstring|vbobject|vberror|vbboolean|vbvariant|vbdataobject|vbdecimal|vbbyte|vbarray)$/',
                    'functions' => '/^((?i)abs|array|asc|atn|cbool|cbyte|ccur|cdate|cdbl|chr|cint|clng|cos|createobject|csng|cstr|date|dateadd|datediff|datepart|dateserial|datevalue|day|escape|eval|exp|filter|formatcurrency|formatdatetime|formatnumber|formatpercent|getlocale|getobject|getref|hex|hour|inputbox|instr|instrrev|int|fix|isarray|isdate|isempty|isnull|isnumeric|isobject|join|lbound|lcase|left|len|loadpicture|log|ltrim|rtrim|trim|mid|minute|month|monthname|msgbox|now|oct|replace|rgb|right|rnd|round|scriptengine|scriptenginebuildversion|scriptenginemajorversion|scriptengineminorversion|second|setlocale|sgn|sin|space|split|sqr|strcomp|string|strreverse|tan|time|timer|timeserial|timevalue|typename|ubound|ucase|unescape|vartype|weekday|weekdayname|year)$/',
                    'builtin' => '/^((?i)debug|err|match|regexp)$/',
                    'reserved' => '/^((?i)empty|false|nothing|null|true|and|eqv|imp|is|mod|not|or|xor|call|class|end|const|public|private|dim|do|while|until|exit|loop|erase|execute|executeglobal|for|each|in|to|step|next|function|default|if|then|else|elseif|on|error|resume|goto|option|explicit|property|get|let|set|randomize|redim|preserve|select|case|stop|sub|wend|with)$/',
                ),
            ),
            0 =>
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 =>
                array (
                ),
                4 =>
                array (
                ),
                5 =>
                array (
                    'constants' => '/^((?i)vbblack|vbred|vbgreen|vbyellow|vbblue|vbmagenta|vbcyan|vbwhite|vbbinarycompare|vbtextcompare|vbsunday|vbmonday|vbtuesday|vbwednesday|vbthursday|vbfriday|vbsaturday|vbusesystemdayofweek|vbfirstjan1|vbfirstfourdays|vbfirstfullweek|vbgeneraldate|vblongdate|vbshortdate|vblongtime|vbshorttime|vbobjecterror|vbokonly|vbokcancel|vbabortretryignore|vbyesnocancel|vbyesno|vbretrycancel|vbcritical|vbquestion|vbexclamation|vbinformation|vbdefaultbutton1|vbdefaultbutton2|vbdefaultbutton3|vbdefaultbutton4|vbapplicationmodal|vbsystemmodal|vbok|vbcancel|vbabort|vbretry|vbignore|vbyes|vbno|vbcr|vbcrlf|vbformfeed|vblf|vbnewline|vbnullchar|vbnullstring|vbtab|vbverticaltab|vbusedefault|vbtrue|vbfalse|vbempty|vbnull|vbinteger|vblong|vbsingle|vbdouble|vbcurrency|vbdate|vbstring|vbobject|vberror|vbboolean|vbvariant|vbdataobject|vbdecimal|vbbyte|vbarray)$/',
                    'functions' => '/^((?i)abs|array|asc|atn|cbool|cbyte|ccur|cdate|cdbl|chr|cint|clng|cos|createobject|csng|cstr|date|dateadd|datediff|datepart|dateserial|datevalue|day|escape|eval|exp|filter|formatcurrency|formatdatetime|formatnumber|formatpercent|getlocale|getobject|getref|hex|hour|inputbox|instr|instrrev|int|fix|isarray|isdate|isempty|isnull|isnumeric|isobject|join|lbound|lcase|left|len|loadpicture|log|ltrim|rtrim|trim|mid|minute|month|monthname|msgbox|now|oct|replace|rgb|right|rnd|round|scriptengine|scriptenginebuildversion|scriptenginemajorversion|scriptengineminorversion|second|setlocale|sgn|sin|space|split|sqr|strcomp|string|strreverse|tan|time|timer|timeserial|timevalue|typename|ubound|ucase|unescape|vartype|weekday|weekdayname|year)$/',
                    'builtin' => '/^((?i)debug|err|match|regexp)$/',
                    'reserved' => '/^((?i)empty|false|nothing|null|true|and|eqv|imp|is|mod|not|or|xor|call|class|end|const|public|private|dim|do|while|until|exit|loop|erase|execute|executeglobal|for|each|in|to|step|next|function|default|if|then|else|elseif|on|error|resume|goto|option|explicit|property|get|let|set|randomize|redim|preserve|select|case|stop|sub|wend|with)$/',
                ),
            ),
            1 =>
            array (
            ),
            2 =>
            array (
                0 =>
                array (
                ),
                1 =>
                array (
                ),
                2 =>
                array (
                ),
                3 =>
                array (
                ),
            ),
        );
        $this->_parts = array (
            0 =>
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => NULL,
            ),
            1 =>
            array (
            ),
            2 =>
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
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
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
            ),
            1 =>
            array (
            ),
            2 =>
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
            'constants' => 'builtin',
            'functions' => 'builtin',
            'builtin' => 'builtin',
            'reserved' => 'reserved',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }

}