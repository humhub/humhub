<?php
/**
 * Auto-generated class. ABAP syntax highlighting 
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
 * @version    generated from: : abap.xml,v 1.1 2007/06/03 02:35:28 ssttoo Exp 
 * @author Stoyan Stefanov <ssttoo@gmail.com>
 *
 */

/**
 * @ignore
 */

/**
 * Auto-generated class. ABAP syntax highlighting
 *
 * @author Stoyan Stefanov <ssttoo@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_ABAP extends Text_Highlighter
{
    var $_language = 'abap';

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
            -1 => '/((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)^\\*|")|((?i)\')|((?i)[a-z_\\-]\\w*)/',
            0 => '/((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)^\\*|")|((?i)\')|((?i)0[xX][\\da-f]+)|((?i)\\d\\d*|\\b0\\b)|((?i)0[0-7]+)|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)[a-z_\\-]\\w*)/',
            1 => '/((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)^\\*|")|((?i)\')|((?i)0[xX][\\da-f]+)|((?i)\\d\\d*|\\b0\\b)|((?i)0[0-7]+)|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)[a-z_\\-]\\w*)/',
            2 => '/((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)^\\*|")|((?i)\')|((?i)0[xX][\\da-f]+)|((?i)\\d\\d*|\\b0\\b)|((?i)0[0-7]+)|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)[a-z_\\-]\\w*)/',
            3 => '//',
            4 => '//',
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
                6 => 0,
                7 => 0,
                8 => 2,
                9 => 0,
            ),
            1 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 2,
                9 => 0,
            ),
            2 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 2,
                9 => 0,
            ),
            3 => 
            array (
            ),
            4 => 
            array (
            ),
        );
        $this->_delim = array (
            -1 => 
            array (
                0 => 'brackets',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'comment',
                4 => 'quotes',
                5 => '',
            ),
            0 => 
            array (
                0 => 'brackets',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'comment',
                4 => 'quotes',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
            ),
            1 => 
            array (
                0 => 'brackets',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'comment',
                4 => 'quotes',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
            ),
            2 => 
            array (
                0 => 'brackets',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'comment',
                4 => 'quotes',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
            ),
            3 => 
            array (
            ),
            4 => 
            array (
            ),
        );
        $this->_inner = array (
            -1 => 
            array (
                0 => 'code',
                1 => 'code',
                2 => 'code',
                3 => 'comment',
                4 => 'string',
                5 => 'identifier',
            ),
            0 => 
            array (
                0 => 'code',
                1 => 'code',
                2 => 'code',
                3 => 'comment',
                4 => 'string',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'number',
                9 => 'identifier',
            ),
            1 => 
            array (
                0 => 'code',
                1 => 'code',
                2 => 'code',
                3 => 'comment',
                4 => 'string',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'number',
                9 => 'identifier',
            ),
            2 => 
            array (
                0 => 'code',
                1 => 'code',
                2 => 'code',
                3 => 'comment',
                4 => 'string',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'number',
                9 => 'identifier',
            ),
            3 => 
            array (
            ),
            4 => 
            array (
            ),
        );
        $this->_end = array (
            0 => '/(?i)\\}/',
            1 => '/(?i)\\)/',
            2 => '/(?i)\\]/',
            3 => '/(?mi)$/',
            4 => '/(?i)\'/',
        );
        $this->_states = array (
            -1 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => -1,
            ),
            0 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => -1,
            ),
            1 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => -1,
            ),
            2 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => -1,
            ),
            3 => 
            array (
            ),
            4 => 
            array (
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
                5 => 
                array (
                    'sy' => '/^((?i)screen-name|screen-group1|screen-group2|screen-group3|screen-group4|screen-required|screen-input|screen-output|screen-intensified|screen-invisible|screen-length|screen-active|sy-index|sy-pagno|sy-tabix|sy-tfill|sy-tlopc|sy-tmaxl|sy-toccu|sy-ttabc|sy-tstis|sy-ttabi|sy-dbcnt|sy-fdpos|sy-colno|sy-linct|sy-linno|sy-linsz|sy-pagct|sy-macol|sy-marow|sy-tleng|sy-sfoff|sy-willi|sy-lilli|sy-subrc|sy-fleng|sy-cucol|sy-curow|sy-lsind|sy-listi|sy-stepl|sy-tpagi|sy-winx1|sy-winy1|sy-winx2|sy-winy2|sy-winco|sy-winro|sy-windi|sy-srows|sy-scols|sy-loopc|sy-folen|sy-fodec|sy-tzone|sy-dayst|sy-ftype|sy-appli|sy-fdayw|sy-ccurs|sy-ccurt|sy-debug|sy-ctype|sy-input|sy-langu|sy-modno|sy-batch|sy-binpt|sy-calld|sy-dynnr|sy-dyngr|sy-newpa|sy-pri40|sy-rstrt|sy-wtitl|sy-cpage|sy-dbnam|sy-mandt|sy-prefx|sy-fmkey|sy-pexpi|sy-prini|sy-primm|sy-prrel|sy-playo|sy-prbig|sy-playp|sy-prnew|sy-prlog|sy-pdest|sy-plist|sy-pauth|sy-prdsn|sy-pnwpa|sy-callr|sy-repi2|sy-rtitl|sy-prrec|sy-prtxt|sy-prabt|sy-lpass|sy-nrpag|sy-paart|sy-prcop|sy-batzs|sy-bspld|sy-brep4|sy-batzo|sy-batzd|sy-batzw|sy-batzm|sy-ctabl|sy-dbsys|sy-dcsys|sy-macdb|sy-sysid|sy-opsys|sy-pfkey|sy-saprl|sy-tcode|sy-ucomm|sy-cfwae|sy-chwae|sy-spono|sy-sponr|sy-waers|sy-cdate|sy-datum|sy-slset|sy-subty|sy-subcs|sy-group|sy-ffile|sy-uzeit|sy-dsnam|sy-repid|sy-tabid|sy-tfdsn|sy-uname|sy-lstat|sy-abcde|sy-marky|sy-sfnam|sy-tname|sy-msgli|sy-title|sy-entry|sy-lisel|sy-uline|sy-xcode|sy-cprog|sy-xprog|sy-xform|sy-ldbpg|sy-tvar0|sy-tvar1|sy-tvar2|sy-tvar3|sy-tvar4|sy-tvar5|sy-tvar6|sy-tvar7|sy-tvar8|sy-tvar9|sy-msgid|sy-msgty|sy-msgno|sy-msgv1|sy-msgv2|sy-msgv3|sy-msgv4|sy-oncom|sy-vline|sy-winsl|sy-staco|sy-staro|sy-datar|sy-host|sy-locdb|sy-locop|sy-datlo|sy-timlo|sy-zonlo|syst-index|syst-pagno|syst-tabix|syst-tfill|syst-tlopc|syst-tmaxl|syst-toccu|syst-ttabc|syst-tstis|syst-ttabi|syst-dbcnt|syst-fdpos|syst-colno|syst-linct|syst-linno|syst-linsz|syst-pagct|syst-macol|syst-marow|syst-tleng|syst-sfoff|syst-willi|syst-lilli|syst-subrc|syst-fleng|syst-cucol|syst-curow|syst-lsind|syst-listi|syst-stepl|syst-tpagi|syst-winx1|syst-winy1|syst-winx2|syst-winy2|syst-winco|syst-winro|syst-windi|syst-srows|syst-scols|syst-loopc|syst-folen|syst-fodec|syst-tzone|syst-dayst|syst-ftype|syst-appli|syst-fdayw|syst-ccurs|syst-ccurt|syst-debug|syst-ctype|syst-input|syst-langu|syst-modno|syst-batch|syst-binpt|syst-calld|syst-dynnr|syst-dyngr|syst-newpa|syst-pri40|syst-rstrt|syst-wtitl|syst-cpage|syst-dbnam|syst-mandt|syst-prefx|syst-fmkey|syst-pexpi|syst-prini|syst-primm|syst-prrel|syst-playo|syst-prbig|syst-playp|syst-prnew|syst-prlog|syst-pdest|syst-plist|syst-pauth|syst-prdsn|syst-pnwpa|syst-callr|syst-repi2|syst-rtitl|syst-prrec|syst-prtxt|syst-prabt|syst-lpass|syst-nrpag|syst-paart|syst-prcop|syst-batzs|syst-bspld|syst-brep4|syst-batzo|syst-batzd|syst-batzw|syst-batzm|syst-ctabl|syst-dbsys|syst-dcsys|syst-macdb|syst-sysid|syst-opsys|syst-pfkey|syst-saprl|syst-tcode|syst-ucomm|syst-cfwae|syst-chwae|syst-spono|syst-sponr|syst-waers|syst-cdate|syst-datum|syst-slset|syst-subty|syst-subcs|syst-group|syst-ffile|syst-uzeit|syst-dsnam|syst-repid|syst-tabid|syst-tfdsn|syst-uname|syst-lstat|syst-abcde|syst-marky|syst-sfnam|syst-tname|syst-msgli|syst-title|syst-entry|syst-lisel|syst-uline|syst-xcode|syst-cprog|syst-xprog|syst-xform|syst-ldbpg|syst-tvar0|syst-tvar1|syst-tvar2|syst-tvar3|syst-tvar4|syst-tvar5|syst-tvar6|syst-tvar7|syst-tvar8|syst-tvar9|syst-msgid|syst-msgty|syst-msgno|syst-msgv1|syst-msgv2|syst-msgv3|syst-msgv4|syst-oncom|syst-vline|syst-winsl|syst-staco|syst-staro|syst-datar|syst-host|syst-locdb|syst-locop|syst-datlo|syst-timlo|syst-zonlo)$/',
                    'reserved' => '/^((?i)abs|acos|add|add-corresponding|adjacent|after|aliases|all|analyzer|and|any|append|as|ascending|asin|assign|assigned|assigning|at|atan|authority-check|avg|back|before|begin|binary|bit|bit-and|bit-not|bit-or|bit-xor|blank|block|break-point|buffer|by|c|call|case|catch|ceil|centered|chain|change|changing|check|checkbox|class|class-data|class-events|class-methods|class-pool|clear|client|close|cnt|code|collect|color|comment|commit|communication|compute|concatenate|condense|constants|context|contexts|continue|control|controls|convert|copy|corresponding|cos|cosh|count|country|create|currency|cursor|customer-function|data|database|dataset|delete|decimals|default|define|demand|descending|describe|dialog|distinct|div|divide|divide-corresponding|do|duplicates|dynpro|edit|editor-call|else|elseif|end|end-of-definition|end-of-page|end-of-selection|endat|endcase|endcatch|endchain|endclass|enddo|endexec|endform|endfunction|endif|endinterface|endloop|endmethod|endmodule|endon|endprovide|endselect|endwhile|entries|events|exec|exit|exit-command|exp|exponent|export|exporting|exceptions|extended|extract|fetch|field|field-groups|field-symbols|fields|floor|for|form|format|frac|frame|free|from|function|function-pool|generate|get|group|hashed|header|help-id|help-request|hide|hotspot|icon|id|if|import|importing|include|index|infotypes|initialization|inner|input|insert|intensified|interface|interface-pool|interfaces|into|inverse|join|key|language|last|leave|left|left-justified|like|line|line-count|line-selection|line-size|lines|list-processing|load|load-of-program|local|locale|log|log10|loop|m|margin|mask|matchcode|max|memory|message|message-id|messages|method|methods|min|mod|mode|modif|modify|module|move|move-corresponding|multiply|multiply-corresponding|new|new-line|new-page|next|no|no-gap|no-gaps|no-heading|no-scrolling|no-sign|no-title|no-zero|nodes|non-unique|o|object|obligatory|occurs|of|off|on|open|or|order|others|outer|output|overlay|pack|page|parameter|parameters|perform|pf-status|position|print|print-control|private|process|program|property|protected|provide|public|put|radiobutton|raise|raising|range|ranges|read|receive|refresh|reject|replace|report|requested|reserve|reset|right-justified|rollback|round|rows|rtti|run|scan|screen|search|separated|scroll|scroll-boundary|select|select-options|selection-screen|selection-table|set|shared|shift|sign|sin|single|sinh|size|skip|sort|sorted|split|sql|sqrt|stamp|standard|start-of-selection|statics|stop|string|strlen|structure|submit|subtract|subtract-corresponding|sum|supply|suppress|symbol|syntax-check|syntax-trace|system-call|system-exceptions|table|table_line|tables|tan|tanh|text|textpool|time|times|title|titlebar|to|top-of-page|transaction|transfer|translate|transporting|trunc|type|type-pool|type-pools|types|uline|under|unique|unit|unpack|up|update|user-command|using|value|value-request|values|vary|when|where|while|window|with|with-title|work|write|x|xstring|z|zone)$/',
                    'constants' => '/^((?i)initial|null|space|col_background|col_heading|col_normal|col_total|col_key|col_positive|col_negative|col_group)$/',
                ),
            ),
            0 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => -1,
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
                9 => 
                array (
                    'sy' => '/^((?i)screen-name|screen-group1|screen-group2|screen-group3|screen-group4|screen-required|screen-input|screen-output|screen-intensified|screen-invisible|screen-length|screen-active|sy-index|sy-pagno|sy-tabix|sy-tfill|sy-tlopc|sy-tmaxl|sy-toccu|sy-ttabc|sy-tstis|sy-ttabi|sy-dbcnt|sy-fdpos|sy-colno|sy-linct|sy-linno|sy-linsz|sy-pagct|sy-macol|sy-marow|sy-tleng|sy-sfoff|sy-willi|sy-lilli|sy-subrc|sy-fleng|sy-cucol|sy-curow|sy-lsind|sy-listi|sy-stepl|sy-tpagi|sy-winx1|sy-winy1|sy-winx2|sy-winy2|sy-winco|sy-winro|sy-windi|sy-srows|sy-scols|sy-loopc|sy-folen|sy-fodec|sy-tzone|sy-dayst|sy-ftype|sy-appli|sy-fdayw|sy-ccurs|sy-ccurt|sy-debug|sy-ctype|sy-input|sy-langu|sy-modno|sy-batch|sy-binpt|sy-calld|sy-dynnr|sy-dyngr|sy-newpa|sy-pri40|sy-rstrt|sy-wtitl|sy-cpage|sy-dbnam|sy-mandt|sy-prefx|sy-fmkey|sy-pexpi|sy-prini|sy-primm|sy-prrel|sy-playo|sy-prbig|sy-playp|sy-prnew|sy-prlog|sy-pdest|sy-plist|sy-pauth|sy-prdsn|sy-pnwpa|sy-callr|sy-repi2|sy-rtitl|sy-prrec|sy-prtxt|sy-prabt|sy-lpass|sy-nrpag|sy-paart|sy-prcop|sy-batzs|sy-bspld|sy-brep4|sy-batzo|sy-batzd|sy-batzw|sy-batzm|sy-ctabl|sy-dbsys|sy-dcsys|sy-macdb|sy-sysid|sy-opsys|sy-pfkey|sy-saprl|sy-tcode|sy-ucomm|sy-cfwae|sy-chwae|sy-spono|sy-sponr|sy-waers|sy-cdate|sy-datum|sy-slset|sy-subty|sy-subcs|sy-group|sy-ffile|sy-uzeit|sy-dsnam|sy-repid|sy-tabid|sy-tfdsn|sy-uname|sy-lstat|sy-abcde|sy-marky|sy-sfnam|sy-tname|sy-msgli|sy-title|sy-entry|sy-lisel|sy-uline|sy-xcode|sy-cprog|sy-xprog|sy-xform|sy-ldbpg|sy-tvar0|sy-tvar1|sy-tvar2|sy-tvar3|sy-tvar4|sy-tvar5|sy-tvar6|sy-tvar7|sy-tvar8|sy-tvar9|sy-msgid|sy-msgty|sy-msgno|sy-msgv1|sy-msgv2|sy-msgv3|sy-msgv4|sy-oncom|sy-vline|sy-winsl|sy-staco|sy-staro|sy-datar|sy-host|sy-locdb|sy-locop|sy-datlo|sy-timlo|sy-zonlo|syst-index|syst-pagno|syst-tabix|syst-tfill|syst-tlopc|syst-tmaxl|syst-toccu|syst-ttabc|syst-tstis|syst-ttabi|syst-dbcnt|syst-fdpos|syst-colno|syst-linct|syst-linno|syst-linsz|syst-pagct|syst-macol|syst-marow|syst-tleng|syst-sfoff|syst-willi|syst-lilli|syst-subrc|syst-fleng|syst-cucol|syst-curow|syst-lsind|syst-listi|syst-stepl|syst-tpagi|syst-winx1|syst-winy1|syst-winx2|syst-winy2|syst-winco|syst-winro|syst-windi|syst-srows|syst-scols|syst-loopc|syst-folen|syst-fodec|syst-tzone|syst-dayst|syst-ftype|syst-appli|syst-fdayw|syst-ccurs|syst-ccurt|syst-debug|syst-ctype|syst-input|syst-langu|syst-modno|syst-batch|syst-binpt|syst-calld|syst-dynnr|syst-dyngr|syst-newpa|syst-pri40|syst-rstrt|syst-wtitl|syst-cpage|syst-dbnam|syst-mandt|syst-prefx|syst-fmkey|syst-pexpi|syst-prini|syst-primm|syst-prrel|syst-playo|syst-prbig|syst-playp|syst-prnew|syst-prlog|syst-pdest|syst-plist|syst-pauth|syst-prdsn|syst-pnwpa|syst-callr|syst-repi2|syst-rtitl|syst-prrec|syst-prtxt|syst-prabt|syst-lpass|syst-nrpag|syst-paart|syst-prcop|syst-batzs|syst-bspld|syst-brep4|syst-batzo|syst-batzd|syst-batzw|syst-batzm|syst-ctabl|syst-dbsys|syst-dcsys|syst-macdb|syst-sysid|syst-opsys|syst-pfkey|syst-saprl|syst-tcode|syst-ucomm|syst-cfwae|syst-chwae|syst-spono|syst-sponr|syst-waers|syst-cdate|syst-datum|syst-slset|syst-subty|syst-subcs|syst-group|syst-ffile|syst-uzeit|syst-dsnam|syst-repid|syst-tabid|syst-tfdsn|syst-uname|syst-lstat|syst-abcde|syst-marky|syst-sfnam|syst-tname|syst-msgli|syst-title|syst-entry|syst-lisel|syst-uline|syst-xcode|syst-cprog|syst-xprog|syst-xform|syst-ldbpg|syst-tvar0|syst-tvar1|syst-tvar2|syst-tvar3|syst-tvar4|syst-tvar5|syst-tvar6|syst-tvar7|syst-tvar8|syst-tvar9|syst-msgid|syst-msgty|syst-msgno|syst-msgv1|syst-msgv2|syst-msgv3|syst-msgv4|syst-oncom|syst-vline|syst-winsl|syst-staco|syst-staro|syst-datar|syst-host|syst-locdb|syst-locop|syst-datlo|syst-timlo|syst-zonlo)$/',
                    'reserved' => '/^((?i)abs|acos|add|add-corresponding|adjacent|after|aliases|all|analyzer|and|any|append|as|ascending|asin|assign|assigned|assigning|at|atan|authority-check|avg|back|before|begin|binary|bit|bit-and|bit-not|bit-or|bit-xor|blank|block|break-point|buffer|by|c|call|case|catch|ceil|centered|chain|change|changing|check|checkbox|class|class-data|class-events|class-methods|class-pool|clear|client|close|cnt|code|collect|color|comment|commit|communication|compute|concatenate|condense|constants|context|contexts|continue|control|controls|convert|copy|corresponding|cos|cosh|count|country|create|currency|cursor|customer-function|data|database|dataset|delete|decimals|default|define|demand|descending|describe|dialog|distinct|div|divide|divide-corresponding|do|duplicates|dynpro|edit|editor-call|else|elseif|end|end-of-definition|end-of-page|end-of-selection|endat|endcase|endcatch|endchain|endclass|enddo|endexec|endform|endfunction|endif|endinterface|endloop|endmethod|endmodule|endon|endprovide|endselect|endwhile|entries|events|exec|exit|exit-command|exp|exponent|export|exporting|exceptions|extended|extract|fetch|field|field-groups|field-symbols|fields|floor|for|form|format|frac|frame|free|from|function|function-pool|generate|get|group|hashed|header|help-id|help-request|hide|hotspot|icon|id|if|import|importing|include|index|infotypes|initialization|inner|input|insert|intensified|interface|interface-pool|interfaces|into|inverse|join|key|language|last|leave|left|left-justified|like|line|line-count|line-selection|line-size|lines|list-processing|load|load-of-program|local|locale|log|log10|loop|m|margin|mask|matchcode|max|memory|message|message-id|messages|method|methods|min|mod|mode|modif|modify|module|move|move-corresponding|multiply|multiply-corresponding|new|new-line|new-page|next|no|no-gap|no-gaps|no-heading|no-scrolling|no-sign|no-title|no-zero|nodes|non-unique|o|object|obligatory|occurs|of|off|on|open|or|order|others|outer|output|overlay|pack|page|parameter|parameters|perform|pf-status|position|print|print-control|private|process|program|property|protected|provide|public|put|radiobutton|raise|raising|range|ranges|read|receive|refresh|reject|replace|report|requested|reserve|reset|right-justified|rollback|round|rows|rtti|run|scan|screen|search|separated|scroll|scroll-boundary|select|select-options|selection-screen|selection-table|set|shared|shift|sign|sin|single|sinh|size|skip|sort|sorted|split|sql|sqrt|stamp|standard|start-of-selection|statics|stop|string|strlen|structure|submit|subtract|subtract-corresponding|sum|supply|suppress|symbol|syntax-check|syntax-trace|system-call|system-exceptions|table|table_line|tables|tan|tanh|text|textpool|time|times|title|titlebar|to|top-of-page|transaction|transfer|translate|transporting|trunc|type|type-pool|type-pools|types|uline|under|unique|unit|unpack|up|update|user-command|using|value|value-request|values|vary|when|where|while|window|with|with-title|work|write|x|xstring|z|zone)$/',
                    'constants' => '/^((?i)initial|null|space|col_background|col_heading|col_normal|col_total|col_key|col_positive|col_negative|col_group)$/',
                ),
            ),
            1 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => -1,
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
                9 => 
                array (
                    'sy' => '/^((?i)screen-name|screen-group1|screen-group2|screen-group3|screen-group4|screen-required|screen-input|screen-output|screen-intensified|screen-invisible|screen-length|screen-active|sy-index|sy-pagno|sy-tabix|sy-tfill|sy-tlopc|sy-tmaxl|sy-toccu|sy-ttabc|sy-tstis|sy-ttabi|sy-dbcnt|sy-fdpos|sy-colno|sy-linct|sy-linno|sy-linsz|sy-pagct|sy-macol|sy-marow|sy-tleng|sy-sfoff|sy-willi|sy-lilli|sy-subrc|sy-fleng|sy-cucol|sy-curow|sy-lsind|sy-listi|sy-stepl|sy-tpagi|sy-winx1|sy-winy1|sy-winx2|sy-winy2|sy-winco|sy-winro|sy-windi|sy-srows|sy-scols|sy-loopc|sy-folen|sy-fodec|sy-tzone|sy-dayst|sy-ftype|sy-appli|sy-fdayw|sy-ccurs|sy-ccurt|sy-debug|sy-ctype|sy-input|sy-langu|sy-modno|sy-batch|sy-binpt|sy-calld|sy-dynnr|sy-dyngr|sy-newpa|sy-pri40|sy-rstrt|sy-wtitl|sy-cpage|sy-dbnam|sy-mandt|sy-prefx|sy-fmkey|sy-pexpi|sy-prini|sy-primm|sy-prrel|sy-playo|sy-prbig|sy-playp|sy-prnew|sy-prlog|sy-pdest|sy-plist|sy-pauth|sy-prdsn|sy-pnwpa|sy-callr|sy-repi2|sy-rtitl|sy-prrec|sy-prtxt|sy-prabt|sy-lpass|sy-nrpag|sy-paart|sy-prcop|sy-batzs|sy-bspld|sy-brep4|sy-batzo|sy-batzd|sy-batzw|sy-batzm|sy-ctabl|sy-dbsys|sy-dcsys|sy-macdb|sy-sysid|sy-opsys|sy-pfkey|sy-saprl|sy-tcode|sy-ucomm|sy-cfwae|sy-chwae|sy-spono|sy-sponr|sy-waers|sy-cdate|sy-datum|sy-slset|sy-subty|sy-subcs|sy-group|sy-ffile|sy-uzeit|sy-dsnam|sy-repid|sy-tabid|sy-tfdsn|sy-uname|sy-lstat|sy-abcde|sy-marky|sy-sfnam|sy-tname|sy-msgli|sy-title|sy-entry|sy-lisel|sy-uline|sy-xcode|sy-cprog|sy-xprog|sy-xform|sy-ldbpg|sy-tvar0|sy-tvar1|sy-tvar2|sy-tvar3|sy-tvar4|sy-tvar5|sy-tvar6|sy-tvar7|sy-tvar8|sy-tvar9|sy-msgid|sy-msgty|sy-msgno|sy-msgv1|sy-msgv2|sy-msgv3|sy-msgv4|sy-oncom|sy-vline|sy-winsl|sy-staco|sy-staro|sy-datar|sy-host|sy-locdb|sy-locop|sy-datlo|sy-timlo|sy-zonlo|syst-index|syst-pagno|syst-tabix|syst-tfill|syst-tlopc|syst-tmaxl|syst-toccu|syst-ttabc|syst-tstis|syst-ttabi|syst-dbcnt|syst-fdpos|syst-colno|syst-linct|syst-linno|syst-linsz|syst-pagct|syst-macol|syst-marow|syst-tleng|syst-sfoff|syst-willi|syst-lilli|syst-subrc|syst-fleng|syst-cucol|syst-curow|syst-lsind|syst-listi|syst-stepl|syst-tpagi|syst-winx1|syst-winy1|syst-winx2|syst-winy2|syst-winco|syst-winro|syst-windi|syst-srows|syst-scols|syst-loopc|syst-folen|syst-fodec|syst-tzone|syst-dayst|syst-ftype|syst-appli|syst-fdayw|syst-ccurs|syst-ccurt|syst-debug|syst-ctype|syst-input|syst-langu|syst-modno|syst-batch|syst-binpt|syst-calld|syst-dynnr|syst-dyngr|syst-newpa|syst-pri40|syst-rstrt|syst-wtitl|syst-cpage|syst-dbnam|syst-mandt|syst-prefx|syst-fmkey|syst-pexpi|syst-prini|syst-primm|syst-prrel|syst-playo|syst-prbig|syst-playp|syst-prnew|syst-prlog|syst-pdest|syst-plist|syst-pauth|syst-prdsn|syst-pnwpa|syst-callr|syst-repi2|syst-rtitl|syst-prrec|syst-prtxt|syst-prabt|syst-lpass|syst-nrpag|syst-paart|syst-prcop|syst-batzs|syst-bspld|syst-brep4|syst-batzo|syst-batzd|syst-batzw|syst-batzm|syst-ctabl|syst-dbsys|syst-dcsys|syst-macdb|syst-sysid|syst-opsys|syst-pfkey|syst-saprl|syst-tcode|syst-ucomm|syst-cfwae|syst-chwae|syst-spono|syst-sponr|syst-waers|syst-cdate|syst-datum|syst-slset|syst-subty|syst-subcs|syst-group|syst-ffile|syst-uzeit|syst-dsnam|syst-repid|syst-tabid|syst-tfdsn|syst-uname|syst-lstat|syst-abcde|syst-marky|syst-sfnam|syst-tname|syst-msgli|syst-title|syst-entry|syst-lisel|syst-uline|syst-xcode|syst-cprog|syst-xprog|syst-xform|syst-ldbpg|syst-tvar0|syst-tvar1|syst-tvar2|syst-tvar3|syst-tvar4|syst-tvar5|syst-tvar6|syst-tvar7|syst-tvar8|syst-tvar9|syst-msgid|syst-msgty|syst-msgno|syst-msgv1|syst-msgv2|syst-msgv3|syst-msgv4|syst-oncom|syst-vline|syst-winsl|syst-staco|syst-staro|syst-datar|syst-host|syst-locdb|syst-locop|syst-datlo|syst-timlo|syst-zonlo)$/',
                    'reserved' => '/^((?i)abs|acos|add|add-corresponding|adjacent|after|aliases|all|analyzer|and|any|append|as|ascending|asin|assign|assigned|assigning|at|atan|authority-check|avg|back|before|begin|binary|bit|bit-and|bit-not|bit-or|bit-xor|blank|block|break-point|buffer|by|c|call|case|catch|ceil|centered|chain|change|changing|check|checkbox|class|class-data|class-events|class-methods|class-pool|clear|client|close|cnt|code|collect|color|comment|commit|communication|compute|concatenate|condense|constants|context|contexts|continue|control|controls|convert|copy|corresponding|cos|cosh|count|country|create|currency|cursor|customer-function|data|database|dataset|delete|decimals|default|define|demand|descending|describe|dialog|distinct|div|divide|divide-corresponding|do|duplicates|dynpro|edit|editor-call|else|elseif|end|end-of-definition|end-of-page|end-of-selection|endat|endcase|endcatch|endchain|endclass|enddo|endexec|endform|endfunction|endif|endinterface|endloop|endmethod|endmodule|endon|endprovide|endselect|endwhile|entries|events|exec|exit|exit-command|exp|exponent|export|exporting|exceptions|extended|extract|fetch|field|field-groups|field-symbols|fields|floor|for|form|format|frac|frame|free|from|function|function-pool|generate|get|group|hashed|header|help-id|help-request|hide|hotspot|icon|id|if|import|importing|include|index|infotypes|initialization|inner|input|insert|intensified|interface|interface-pool|interfaces|into|inverse|join|key|language|last|leave|left|left-justified|like|line|line-count|line-selection|line-size|lines|list-processing|load|load-of-program|local|locale|log|log10|loop|m|margin|mask|matchcode|max|memory|message|message-id|messages|method|methods|min|mod|mode|modif|modify|module|move|move-corresponding|multiply|multiply-corresponding|new|new-line|new-page|next|no|no-gap|no-gaps|no-heading|no-scrolling|no-sign|no-title|no-zero|nodes|non-unique|o|object|obligatory|occurs|of|off|on|open|or|order|others|outer|output|overlay|pack|page|parameter|parameters|perform|pf-status|position|print|print-control|private|process|program|property|protected|provide|public|put|radiobutton|raise|raising|range|ranges|read|receive|refresh|reject|replace|report|requested|reserve|reset|right-justified|rollback|round|rows|rtti|run|scan|screen|search|separated|scroll|scroll-boundary|select|select-options|selection-screen|selection-table|set|shared|shift|sign|sin|single|sinh|size|skip|sort|sorted|split|sql|sqrt|stamp|standard|start-of-selection|statics|stop|string|strlen|structure|submit|subtract|subtract-corresponding|sum|supply|suppress|symbol|syntax-check|syntax-trace|system-call|system-exceptions|table|table_line|tables|tan|tanh|text|textpool|time|times|title|titlebar|to|top-of-page|transaction|transfer|translate|transporting|trunc|type|type-pool|type-pools|types|uline|under|unique|unit|unpack|up|update|user-command|using|value|value-request|values|vary|when|where|while|window|with|with-title|work|write|x|xstring|z|zone)$/',
                    'constants' => '/^((?i)initial|null|space|col_background|col_heading|col_normal|col_total|col_key|col_positive|col_negative|col_group)$/',
                ),
            ),
            2 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => -1,
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
                9 => 
                array (
                    'sy' => '/^((?i)screen-name|screen-group1|screen-group2|screen-group3|screen-group4|screen-required|screen-input|screen-output|screen-intensified|screen-invisible|screen-length|screen-active|sy-index|sy-pagno|sy-tabix|sy-tfill|sy-tlopc|sy-tmaxl|sy-toccu|sy-ttabc|sy-tstis|sy-ttabi|sy-dbcnt|sy-fdpos|sy-colno|sy-linct|sy-linno|sy-linsz|sy-pagct|sy-macol|sy-marow|sy-tleng|sy-sfoff|sy-willi|sy-lilli|sy-subrc|sy-fleng|sy-cucol|sy-curow|sy-lsind|sy-listi|sy-stepl|sy-tpagi|sy-winx1|sy-winy1|sy-winx2|sy-winy2|sy-winco|sy-winro|sy-windi|sy-srows|sy-scols|sy-loopc|sy-folen|sy-fodec|sy-tzone|sy-dayst|sy-ftype|sy-appli|sy-fdayw|sy-ccurs|sy-ccurt|sy-debug|sy-ctype|sy-input|sy-langu|sy-modno|sy-batch|sy-binpt|sy-calld|sy-dynnr|sy-dyngr|sy-newpa|sy-pri40|sy-rstrt|sy-wtitl|sy-cpage|sy-dbnam|sy-mandt|sy-prefx|sy-fmkey|sy-pexpi|sy-prini|sy-primm|sy-prrel|sy-playo|sy-prbig|sy-playp|sy-prnew|sy-prlog|sy-pdest|sy-plist|sy-pauth|sy-prdsn|sy-pnwpa|sy-callr|sy-repi2|sy-rtitl|sy-prrec|sy-prtxt|sy-prabt|sy-lpass|sy-nrpag|sy-paart|sy-prcop|sy-batzs|sy-bspld|sy-brep4|sy-batzo|sy-batzd|sy-batzw|sy-batzm|sy-ctabl|sy-dbsys|sy-dcsys|sy-macdb|sy-sysid|sy-opsys|sy-pfkey|sy-saprl|sy-tcode|sy-ucomm|sy-cfwae|sy-chwae|sy-spono|sy-sponr|sy-waers|sy-cdate|sy-datum|sy-slset|sy-subty|sy-subcs|sy-group|sy-ffile|sy-uzeit|sy-dsnam|sy-repid|sy-tabid|sy-tfdsn|sy-uname|sy-lstat|sy-abcde|sy-marky|sy-sfnam|sy-tname|sy-msgli|sy-title|sy-entry|sy-lisel|sy-uline|sy-xcode|sy-cprog|sy-xprog|sy-xform|sy-ldbpg|sy-tvar0|sy-tvar1|sy-tvar2|sy-tvar3|sy-tvar4|sy-tvar5|sy-tvar6|sy-tvar7|sy-tvar8|sy-tvar9|sy-msgid|sy-msgty|sy-msgno|sy-msgv1|sy-msgv2|sy-msgv3|sy-msgv4|sy-oncom|sy-vline|sy-winsl|sy-staco|sy-staro|sy-datar|sy-host|sy-locdb|sy-locop|sy-datlo|sy-timlo|sy-zonlo|syst-index|syst-pagno|syst-tabix|syst-tfill|syst-tlopc|syst-tmaxl|syst-toccu|syst-ttabc|syst-tstis|syst-ttabi|syst-dbcnt|syst-fdpos|syst-colno|syst-linct|syst-linno|syst-linsz|syst-pagct|syst-macol|syst-marow|syst-tleng|syst-sfoff|syst-willi|syst-lilli|syst-subrc|syst-fleng|syst-cucol|syst-curow|syst-lsind|syst-listi|syst-stepl|syst-tpagi|syst-winx1|syst-winy1|syst-winx2|syst-winy2|syst-winco|syst-winro|syst-windi|syst-srows|syst-scols|syst-loopc|syst-folen|syst-fodec|syst-tzone|syst-dayst|syst-ftype|syst-appli|syst-fdayw|syst-ccurs|syst-ccurt|syst-debug|syst-ctype|syst-input|syst-langu|syst-modno|syst-batch|syst-binpt|syst-calld|syst-dynnr|syst-dyngr|syst-newpa|syst-pri40|syst-rstrt|syst-wtitl|syst-cpage|syst-dbnam|syst-mandt|syst-prefx|syst-fmkey|syst-pexpi|syst-prini|syst-primm|syst-prrel|syst-playo|syst-prbig|syst-playp|syst-prnew|syst-prlog|syst-pdest|syst-plist|syst-pauth|syst-prdsn|syst-pnwpa|syst-callr|syst-repi2|syst-rtitl|syst-prrec|syst-prtxt|syst-prabt|syst-lpass|syst-nrpag|syst-paart|syst-prcop|syst-batzs|syst-bspld|syst-brep4|syst-batzo|syst-batzd|syst-batzw|syst-batzm|syst-ctabl|syst-dbsys|syst-dcsys|syst-macdb|syst-sysid|syst-opsys|syst-pfkey|syst-saprl|syst-tcode|syst-ucomm|syst-cfwae|syst-chwae|syst-spono|syst-sponr|syst-waers|syst-cdate|syst-datum|syst-slset|syst-subty|syst-subcs|syst-group|syst-ffile|syst-uzeit|syst-dsnam|syst-repid|syst-tabid|syst-tfdsn|syst-uname|syst-lstat|syst-abcde|syst-marky|syst-sfnam|syst-tname|syst-msgli|syst-title|syst-entry|syst-lisel|syst-uline|syst-xcode|syst-cprog|syst-xprog|syst-xform|syst-ldbpg|syst-tvar0|syst-tvar1|syst-tvar2|syst-tvar3|syst-tvar4|syst-tvar5|syst-tvar6|syst-tvar7|syst-tvar8|syst-tvar9|syst-msgid|syst-msgty|syst-msgno|syst-msgv1|syst-msgv2|syst-msgv3|syst-msgv4|syst-oncom|syst-vline|syst-winsl|syst-staco|syst-staro|syst-datar|syst-host|syst-locdb|syst-locop|syst-datlo|syst-timlo|syst-zonlo)$/',
                    'reserved' => '/^((?i)abs|acos|add|add-corresponding|adjacent|after|aliases|all|analyzer|and|any|append|as|ascending|asin|assign|assigned|assigning|at|atan|authority-check|avg|back|before|begin|binary|bit|bit-and|bit-not|bit-or|bit-xor|blank|block|break-point|buffer|by|c|call|case|catch|ceil|centered|chain|change|changing|check|checkbox|class|class-data|class-events|class-methods|class-pool|clear|client|close|cnt|code|collect|color|comment|commit|communication|compute|concatenate|condense|constants|context|contexts|continue|control|controls|convert|copy|corresponding|cos|cosh|count|country|create|currency|cursor|customer-function|data|database|dataset|delete|decimals|default|define|demand|descending|describe|dialog|distinct|div|divide|divide-corresponding|do|duplicates|dynpro|edit|editor-call|else|elseif|end|end-of-definition|end-of-page|end-of-selection|endat|endcase|endcatch|endchain|endclass|enddo|endexec|endform|endfunction|endif|endinterface|endloop|endmethod|endmodule|endon|endprovide|endselect|endwhile|entries|events|exec|exit|exit-command|exp|exponent|export|exporting|exceptions|extended|extract|fetch|field|field-groups|field-symbols|fields|floor|for|form|format|frac|frame|free|from|function|function-pool|generate|get|group|hashed|header|help-id|help-request|hide|hotspot|icon|id|if|import|importing|include|index|infotypes|initialization|inner|input|insert|intensified|interface|interface-pool|interfaces|into|inverse|join|key|language|last|leave|left|left-justified|like|line|line-count|line-selection|line-size|lines|list-processing|load|load-of-program|local|locale|log|log10|loop|m|margin|mask|matchcode|max|memory|message|message-id|messages|method|methods|min|mod|mode|modif|modify|module|move|move-corresponding|multiply|multiply-corresponding|new|new-line|new-page|next|no|no-gap|no-gaps|no-heading|no-scrolling|no-sign|no-title|no-zero|nodes|non-unique|o|object|obligatory|occurs|of|off|on|open|or|order|others|outer|output|overlay|pack|page|parameter|parameters|perform|pf-status|position|print|print-control|private|process|program|property|protected|provide|public|put|radiobutton|raise|raising|range|ranges|read|receive|refresh|reject|replace|report|requested|reserve|reset|right-justified|rollback|round|rows|rtti|run|scan|screen|search|separated|scroll|scroll-boundary|select|select-options|selection-screen|selection-table|set|shared|shift|sign|sin|single|sinh|size|skip|sort|sorted|split|sql|sqrt|stamp|standard|start-of-selection|statics|stop|string|strlen|structure|submit|subtract|subtract-corresponding|sum|supply|suppress|symbol|syntax-check|syntax-trace|system-call|system-exceptions|table|table_line|tables|tan|tanh|text|textpool|time|times|title|titlebar|to|top-of-page|transaction|transfer|translate|transporting|trunc|type|type-pool|type-pools|types|uline|under|unique|unit|unpack|up|update|user-command|using|value|value-request|values|vary|when|where|while|window|with|with-title|work|write|x|xstring|z|zone)$/',
                    'constants' => '/^((?i)initial|null|space|col_background|col_heading|col_normal|col_total|col_key|col_positive|col_negative|col_group)$/',
                ),
            ),
            3 => 
            array (
            ),
            4 => 
            array (
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
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
            ),
            1 => 
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
                9 => NULL,
            ),
            3 => 
            array (
            ),
            4 => 
            array (
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
                6 => false,
                7 => false,
                8 => false,
                9 => false,
            ),
            1 => 
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
                9 => false,
            ),
            3 => 
            array (
            ),
            4 => 
            array (
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
            'sy' => 'reserved',
            'reserved' => 'reserved',
            'constants' => 'reserved',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }
    
}