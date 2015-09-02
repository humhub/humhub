<?php
/**
 * Auto-generated class. PERL syntax highlighting
 * 
 * This highlighter is EXPERIMENTAL, so that it may work incorrectly. 
 * Most rules were created by Mariusz Jakubowski, and extended by me.
 * My  knowledge  of  Perl  is  poor,  and  Perl  syntax  seems  too
 * complicated to me. 
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
 * @version    generated from: : perl.xml,v 1.1 2007/06/03 02:35:28 ssttoo Exp 
 * @author Mariusz 'kg' Jakubowski <kg@alternatywa.info>
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */

/**
 * Auto-generated class. PERL syntax highlighting
 *
 * @author Mariusz 'kg' Jakubowski <kg@alternatywa.info>
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_PERL extends Text_Highlighter
{
    var $_language = 'perl';

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
            -1 => '/((?m)^(#!)(.*))|((?m)^=\\w+)|(\\{)|(\\()|(\\[)|((use)\\s+([\\w:]*))|([& ](\\w{2,}::)+\\w{2,})|((?Us)\\b(q[wq]\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|((?Us)\\b(q\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|(#.*)|((?x)(s|tr) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2)((\\\\.|[^\\\\])*?)(\\2[ecgimosx]*))|((?x)(m) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2[ecgimosx]*))|( \\/)|(\\$#?[1-9\'`@!])|((?i)(\\$#?|[@%*])([a-z1-9_]+::)*([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)(&|\\w+)\'[\\w_\']+\\b)|((?i)(\\{)([a-z1-9]+)(\\}))|((?i)[\\$@%]#?\\{[a-z1-9]+\\})|(`)|(\')|(")|((?i)[a-z_]\\w*)|(\\d*\\.?\\d+)/',
            0 => '//',
            1 => '/((?m)^(#!)(.*))|((?m)^=\\w+)|(\\{)|(\\()|(\\[)|((use)\\s+([\\w:]*))|([& ](\\w{2,}::)+\\w{2,})|((?Us)\\b(q[wq]\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|((?Us)\\b(q\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|(#.*)|((?x)(s|tr) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2)((\\\\.|[^\\\\])*?)(\\2[ecgimosx]*))|((?x)(m) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2[ecgimosx]*))|( \\/)|(\\$#?[1-9\'`@!])|((?i)(\\$#?|[@%*])([a-z1-9_]+::)*([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)(&|\\w+)\'[\\w_\']+\\b)|((?i)(\\{)([a-z1-9]+)(\\}))|((?i)[\\$@%]#?\\{[a-z1-9]+\\})|(`)|(\')|(")|((?i)[a-z_]\\w*)|(\\d*\\.?\\d+)/',
            2 => '/((?m)^(#!)(.*))|((?m)^=\\w+)|(\\{)|(\\()|(\\[)|((use)\\s+([\\w:]*))|([& ](\\w{2,}::)+\\w{2,})|((?Us)\\b(q[wq]\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|((?Us)\\b(q\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|(#.*)|((?x)(s|tr) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2)((\\\\.|[^\\\\])*?)(\\2[ecgimosx]*))|((?x)(m) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2[ecgimosx]*))|( \\/)|((?i)([a-z1-9_]+)(\\s*=>))|(\\$#?[1-9\'`@!])|((?i)(\\$#?|[@%*])([a-z1-9_]+::)*([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)(&|\\w+)\'[\\w_\']+\\b)|((?i)(\\{)([a-z1-9]+)(\\}))|((?i)[\\$@%]#?\\{[a-z1-9]+\\})|(`)|(\')|(")|((?i)[a-z_]\\w*)|(\\d*\\.?\\d+)/',
            3 => '/((?m)^(#!)(.*))|((?m)^=\\w+)|(\\{)|(\\()|(\\[)|((use)\\s+([\\w:]*))|([& ](\\w{2,}::)+\\w{2,})|((?Us)\\b(q[wq]\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|((?Us)\\b(q\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7))))|(#.*)|((?x)(s|tr) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2)((\\\\.|[^\\\\])*?)(\\2[ecgimosx]*))|((?x)(m) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2[ecgimosx]*))|( \\/)|(\\$#?[1-9\'`@!])|((?i)(\\$#?|[@%*])([a-z1-9_]+::)*([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)(&|\\w+)\'[\\w_\']+\\b)|((?i)(\\{)([a-z1-9]+)(\\}))|((?i)[\\$@%]#?\\{[a-z1-9]+\\})|(`)|(\')|(")|((?i)[a-z_]\\w*)|(\\d*\\.?\\d+)/',
            4 => '/(\\$#?[1-9\'`@!])|((?i)\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)[\\$@%]#?\\{[a-z1-9]+\\})|(\\\\[\\\\"\'`tnr\\$\\{@])/',
            5 => '/(\\\\\\\\|\\\\"|\\\\\'|\\\\`)/',
            6 => '/(\\\\\\/)/',
            7 => '/(\\$#?[1-9\'`@!])|((?i)\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)[\\$@%]#?\\{[a-z1-9]+\\})|(\\\\\\\\|\\\\"|\\\\\'|\\\\`)/',
            8 => '/(\\\\\\\\|\\\\"|\\\\\'|\\\\`)/',
            9 => '/(\\$#?[1-9\'`@!])|((?i)\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i)))|((?i)[\\$@%]#?\\{[a-z1-9]+\\})|(\\\\[\\\\"\'`tnr\\$\\{@])/',
        );
        $this->_counts = array (
            -1 => 
            array (
                0 => 2,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 2,
                6 => 1,
                7 => 9,
                8 => 9,
                9 => 0,
                10 => 8,
                11 => 5,
                12 => 0,
                13 => 0,
                14 => 3,
                15 => 1,
                16 => 1,
                17 => 3,
                18 => 0,
                19 => 0,
                20 => 0,
                21 => 0,
                22 => 0,
                23 => 0,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => 2,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 2,
                6 => 1,
                7 => 9,
                8 => 9,
                9 => 0,
                10 => 8,
                11 => 5,
                12 => 0,
                13 => 0,
                14 => 3,
                15 => 1,
                16 => 1,
                17 => 3,
                18 => 0,
                19 => 0,
                20 => 0,
                21 => 0,
                22 => 0,
                23 => 0,
            ),
            2 => 
            array (
                0 => 2,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 2,
                6 => 1,
                7 => 9,
                8 => 9,
                9 => 0,
                10 => 8,
                11 => 5,
                12 => 0,
                13 => 2,
                14 => 0,
                15 => 3,
                16 => 1,
                17 => 1,
                18 => 3,
                19 => 0,
                20 => 0,
                21 => 0,
                22 => 0,
                23 => 0,
                24 => 0,
            ),
            3 => 
            array (
                0 => 2,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 2,
                6 => 1,
                7 => 9,
                8 => 9,
                9 => 0,
                10 => 8,
                11 => 5,
                12 => 0,
                13 => 0,
                14 => 3,
                15 => 1,
                16 => 1,
                17 => 3,
                18 => 0,
                19 => 0,
                20 => 0,
                21 => 0,
                22 => 0,
                23 => 0,
            ),
            4 => 
            array (
                0 => 0,
                1 => 1,
                2 => 0,
                3 => 0,
            ),
            5 => 
            array (
                0 => 0,
            ),
            6 => 
            array (
                0 => 0,
            ),
            7 => 
            array (
                0 => 0,
                1 => 1,
                2 => 0,
                3 => 0,
            ),
            8 => 
            array (
                0 => 0,
            ),
            9 => 
            array (
                0 => 0,
                1 => 1,
                2 => 0,
                3 => 0,
            ),
        );
        $this->_delim = array (
            -1 => 
            array (
                0 => '',
                1 => 'comment',
                2 => 'brackets',
                3 => 'brackets',
                4 => 'brackets',
                5 => '',
                6 => '',
                7 => 'quotes',
                8 => 'quotes',
                9 => '',
                10 => '',
                11 => '',
                12 => 'quotes',
                13 => '',
                14 => '',
                15 => '',
                16 => '',
                17 => '',
                18 => '',
                19 => 'quotes',
                20 => 'quotes',
                21 => 'quotes',
                22 => '',
                23 => '',
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => '',
                1 => 'comment',
                2 => 'brackets',
                3 => 'brackets',
                4 => 'brackets',
                5 => '',
                6 => '',
                7 => 'quotes',
                8 => 'quotes',
                9 => '',
                10 => '',
                11 => '',
                12 => 'quotes',
                13 => '',
                14 => '',
                15 => '',
                16 => '',
                17 => '',
                18 => '',
                19 => 'quotes',
                20 => 'quotes',
                21 => 'quotes',
                22 => '',
                23 => '',
            ),
            2 => 
            array (
                0 => '',
                1 => 'comment',
                2 => 'brackets',
                3 => 'brackets',
                4 => 'brackets',
                5 => '',
                6 => '',
                7 => 'quotes',
                8 => 'quotes',
                9 => '',
                10 => '',
                11 => '',
                12 => 'quotes',
                13 => '',
                14 => '',
                15 => '',
                16 => '',
                17 => '',
                18 => '',
                19 => '',
                20 => 'quotes',
                21 => 'quotes',
                22 => 'quotes',
                23 => '',
                24 => '',
            ),
            3 => 
            array (
                0 => '',
                1 => 'comment',
                2 => 'brackets',
                3 => 'brackets',
                4 => 'brackets',
                5 => '',
                6 => '',
                7 => 'quotes',
                8 => 'quotes',
                9 => '',
                10 => '',
                11 => '',
                12 => 'quotes',
                13 => '',
                14 => '',
                15 => '',
                16 => '',
                17 => '',
                18 => '',
                19 => 'quotes',
                20 => 'quotes',
                21 => 'quotes',
                22 => '',
                23 => '',
            ),
            4 => 
            array (
                0 => '',
                1 => '',
                2 => '',
                3 => '',
            ),
            5 => 
            array (
                0 => '',
            ),
            6 => 
            array (
                0 => '',
            ),
            7 => 
            array (
                0 => '',
                1 => '',
                2 => '',
                3 => '',
            ),
            8 => 
            array (
                0 => '',
            ),
            9 => 
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
                0 => 'special',
                1 => 'comment',
                2 => 'code',
                3 => 'code',
                4 => 'code',
                5 => 'special',
                6 => 'special',
                7 => 'string',
                8 => 'string',
                9 => 'comment',
                10 => 'string',
                11 => 'string',
                12 => 'string',
                13 => 'var',
                14 => 'var',
                15 => 'var',
                16 => 'var',
                17 => 'var',
                18 => 'var',
                19 => 'string',
                20 => 'string',
                21 => 'string',
                22 => 'identifier',
                23 => 'number',
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => 'special',
                1 => 'comment',
                2 => 'code',
                3 => 'code',
                4 => 'code',
                5 => 'special',
                6 => 'special',
                7 => 'string',
                8 => 'string',
                9 => 'comment',
                10 => 'string',
                11 => 'string',
                12 => 'string',
                13 => 'var',
                14 => 'var',
                15 => 'var',
                16 => 'var',
                17 => 'var',
                18 => 'var',
                19 => 'string',
                20 => 'string',
                21 => 'string',
                22 => 'identifier',
                23 => 'number',
            ),
            2 => 
            array (
                0 => 'special',
                1 => 'comment',
                2 => 'code',
                3 => 'code',
                4 => 'code',
                5 => 'special',
                6 => 'special',
                7 => 'string',
                8 => 'string',
                9 => 'comment',
                10 => 'string',
                11 => 'string',
                12 => 'string',
                13 => 'string',
                14 => 'var',
                15 => 'var',
                16 => 'var',
                17 => 'var',
                18 => 'var',
                19 => 'var',
                20 => 'string',
                21 => 'string',
                22 => 'string',
                23 => 'identifier',
                24 => 'number',
            ),
            3 => 
            array (
                0 => 'special',
                1 => 'comment',
                2 => 'code',
                3 => 'code',
                4 => 'code',
                5 => 'special',
                6 => 'special',
                7 => 'string',
                8 => 'string',
                9 => 'comment',
                10 => 'string',
                11 => 'string',
                12 => 'string',
                13 => 'var',
                14 => 'var',
                15 => 'var',
                16 => 'var',
                17 => 'var',
                18 => 'var',
                19 => 'string',
                20 => 'string',
                21 => 'string',
                22 => 'identifier',
                23 => 'number',
            ),
            4 => 
            array (
                0 => 'var',
                1 => 'var',
                2 => 'var',
                3 => 'special',
            ),
            5 => 
            array (
                0 => 'special',
            ),
            6 => 
            array (
                0 => 'string',
            ),
            7 => 
            array (
                0 => 'var',
                1 => 'var',
                2 => 'var',
                3 => 'special',
            ),
            8 => 
            array (
                0 => 'special',
            ),
            9 => 
            array (
                0 => 'var',
                1 => 'var',
                2 => 'var',
                3 => 'special',
            ),
        );
        $this->_end = array (
            0 => '/(?m)^=cut[^\\n]*/',
            1 => '/\\}/',
            2 => '/\\)/',
            3 => '/\\]/',
            4 => '/%b2%/',
            5 => '/%b2%/',
            6 => '/\\/[cgimosx]*/',
            7 => '/`/',
            8 => '/\'/',
            9 => '/"/',
        );
        $this->_states = array (
            -1 => 
            array (
                0 => -1,
                1 => 0,
                2 => 1,
                3 => 2,
                4 => 3,
                5 => -1,
                6 => -1,
                7 => 4,
                8 => 5,
                9 => -1,
                10 => -1,
                11 => -1,
                12 => 6,
                13 => -1,
                14 => -1,
                15 => -1,
                16 => -1,
                17 => -1,
                18 => -1,
                19 => 7,
                20 => 8,
                21 => 9,
                22 => -1,
                23 => -1,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
                0 => -1,
                1 => 0,
                2 => 1,
                3 => 2,
                4 => 3,
                5 => -1,
                6 => -1,
                7 => 4,
                8 => 5,
                9 => -1,
                10 => -1,
                11 => -1,
                12 => 6,
                13 => -1,
                14 => -1,
                15 => -1,
                16 => -1,
                17 => -1,
                18 => -1,
                19 => 7,
                20 => 8,
                21 => 9,
                22 => -1,
                23 => -1,
            ),
            2 => 
            array (
                0 => -1,
                1 => 0,
                2 => 1,
                3 => 2,
                4 => 3,
                5 => -1,
                6 => -1,
                7 => 4,
                8 => 5,
                9 => -1,
                10 => -1,
                11 => -1,
                12 => 6,
                13 => -1,
                14 => -1,
                15 => -1,
                16 => -1,
                17 => -1,
                18 => -1,
                19 => -1,
                20 => 7,
                21 => 8,
                22 => 9,
                23 => -1,
                24 => -1,
            ),
            3 => 
            array (
                0 => -1,
                1 => 0,
                2 => 1,
                3 => 2,
                4 => 3,
                5 => -1,
                6 => -1,
                7 => 4,
                8 => 5,
                9 => -1,
                10 => -1,
                11 => -1,
                12 => 6,
                13 => -1,
                14 => -1,
                15 => -1,
                16 => -1,
                17 => -1,
                18 => -1,
                19 => 7,
                20 => 8,
                21 => 9,
                22 => -1,
                23 => -1,
            ),
            4 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
            ),
            5 => 
            array (
                0 => -1,
            ),
            6 => 
            array (
                0 => -1,
            ),
            7 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
            ),
            8 => 
            array (
                0 => -1,
            ),
            9 => 
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
                0 => 
                array (
                ),
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
                7 => -1,
                8 => -1,
                9 => 
                array (
                ),
                10 => 
                array (
                ),
                11 => 
                array (
                ),
                12 => -1,
                13 => 
                array (
                ),
                14 => 
                array (
                ),
                15 => 
                array (
                ),
                16 => 
                array (
                ),
                17 => 
                array (
                ),
                18 => 
                array (
                ),
                19 => -1,
                20 => -1,
                21 => -1,
                22 => 
                array (
                    'reserved' => '/^(abs|accept|alarm|atan2|bind|binmode|bless|caller|chdir|chmod|chomp|chop|chown|chr|chroot|close|closedir|connect|continue|cos|crypt|dbmclose|dbmopen|defined|delete|die|do|dump|each|endgrent|endhostent|endnetent|endprotoent|endpwent|endservent|eof|eval|exec|exists|exit|exp|fcntl|fileno|flock|fork|format|formline|getc|getgrent|getgrgid|getgrnam|gethostbyaddr|gethostbyname|gethostent|getlogin|getnetbyaddr|getnetbyname|getnetent|getpeername|getpgrp|getppid|getpriority|getprotobyname|getprotobynumber|getprotoent|getpwent|getpwnam|getpwuid|getservbyname|getservbyport|getservent|getsockname|getsockopt|glob|gmtime|goto|grep|hex|import|index|int|ioctl|join|keys|kill|last|lc|lcfirst|length|link|listen|local|localtime|lock|log|lstat|map|mkdir|msgctl|msgget|msgrcv|msgsnd|my|next|no|oct|open|opendir|ord|our|pack|package|pipe|pop|pos|print|printf|prototype|push|quotemeta|rand|read|readdir|readline|readlink|readpipe|recv|redo|ref|rename|require|reset|return|reverse|rewinddir|rindex|rmdir|scalar|seek|seekdir|select|semctl|semget|semop|send|setgrent|sethostent|setnetent|setpgrp|setpriority|setprotoent|setpwent|setservent|setsockopt|shift|shmctl|shmget|shmread|shmwrite|shutdown|sin|sleep|socket|socketpair|sort|splice|split|sprintf|sqrt|srand|stat|study|sub|substr|symlink|syscall|sysopen|sysread|sysseek|system|syswrite|tell|telldir|tie|tied|time|times|truncate|uc|ucfirst|umask|undef|unlink|unpack|unshift|untie|use|utime|values|vec|wait|waitpid|wantarray|warn|write|y)$/',
                    'missingreserved' => '/^(new)$/',
                    'flowcontrol' => '/^(if|else|elsif|while|unless|for|foreach|until|do|continue|not|or|and|eq|ne|gt|lt)$/',
                ),
                23 => 
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
                7 => -1,
                8 => -1,
                9 => 
                array (
                ),
                10 => 
                array (
                ),
                11 => 
                array (
                ),
                12 => -1,
                13 => 
                array (
                ),
                14 => 
                array (
                ),
                15 => 
                array (
                ),
                16 => 
                array (
                ),
                17 => 
                array (
                ),
                18 => 
                array (
                ),
                19 => -1,
                20 => -1,
                21 => -1,
                22 => 
                array (
                    'reserved' => '/^(abs|accept|alarm|atan2|bind|binmode|bless|caller|chdir|chmod|chomp|chop|chown|chr|chroot|close|closedir|connect|continue|cos|crypt|dbmclose|dbmopen|defined|delete|die|do|dump|each|endgrent|endhostent|endnetent|endprotoent|endpwent|endservent|eof|eval|exec|exists|exit|exp|fcntl|fileno|flock|fork|format|formline|getc|getgrent|getgrgid|getgrnam|gethostbyaddr|gethostbyname|gethostent|getlogin|getnetbyaddr|getnetbyname|getnetent|getpeername|getpgrp|getppid|getpriority|getprotobyname|getprotobynumber|getprotoent|getpwent|getpwnam|getpwuid|getservbyname|getservbyport|getservent|getsockname|getsockopt|glob|gmtime|goto|grep|hex|import|index|int|ioctl|join|keys|kill|last|lc|lcfirst|length|link|listen|local|localtime|lock|log|lstat|map|mkdir|msgctl|msgget|msgrcv|msgsnd|my|next|no|oct|open|opendir|ord|our|pack|package|pipe|pop|pos|print|printf|prototype|push|quotemeta|rand|read|readdir|readline|readlink|readpipe|recv|redo|ref|rename|require|reset|return|reverse|rewinddir|rindex|rmdir|scalar|seek|seekdir|select|semctl|semget|semop|send|setgrent|sethostent|setnetent|setpgrp|setpriority|setprotoent|setpwent|setservent|setsockopt|shift|shmctl|shmget|shmread|shmwrite|shutdown|sin|sleep|socket|socketpair|sort|splice|split|sprintf|sqrt|srand|stat|study|sub|substr|symlink|syscall|sysopen|sysread|sysseek|system|syswrite|tell|telldir|tie|tied|time|times|truncate|uc|ucfirst|umask|undef|unlink|unpack|unshift|untie|use|utime|values|vec|wait|waitpid|wantarray|warn|write|y)$/',
                    'missingreserved' => '/^(new)$/',
                    'flowcontrol' => '/^(if|else|elsif|while|unless|for|foreach|until|do|continue|not|or|and|eq|ne|gt|lt)$/',
                ),
                23 => 
                array (
                ),
            ),
            2 => 
            array (
                0 => 
                array (
                ),
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
                7 => -1,
                8 => -1,
                9 => 
                array (
                ),
                10 => 
                array (
                ),
                11 => 
                array (
                ),
                12 => -1,
                13 => 
                array (
                ),
                14 => 
                array (
                ),
                15 => 
                array (
                ),
                16 => 
                array (
                ),
                17 => 
                array (
                ),
                18 => 
                array (
                ),
                19 => 
                array (
                ),
                20 => -1,
                21 => -1,
                22 => -1,
                23 => 
                array (
                    'reserved' => '/^(abs|accept|alarm|atan2|bind|binmode|bless|caller|chdir|chmod|chomp|chop|chown|chr|chroot|close|closedir|connect|continue|cos|crypt|dbmclose|dbmopen|defined|delete|die|do|dump|each|endgrent|endhostent|endnetent|endprotoent|endpwent|endservent|eof|eval|exec|exists|exit|exp|fcntl|fileno|flock|fork|format|formline|getc|getgrent|getgrgid|getgrnam|gethostbyaddr|gethostbyname|gethostent|getlogin|getnetbyaddr|getnetbyname|getnetent|getpeername|getpgrp|getppid|getpriority|getprotobyname|getprotobynumber|getprotoent|getpwent|getpwnam|getpwuid|getservbyname|getservbyport|getservent|getsockname|getsockopt|glob|gmtime|goto|grep|hex|import|index|int|ioctl|join|keys|kill|last|lc|lcfirst|length|link|listen|local|localtime|lock|log|lstat|map|mkdir|msgctl|msgget|msgrcv|msgsnd|my|next|no|oct|open|opendir|ord|our|pack|package|pipe|pop|pos|print|printf|prototype|push|quotemeta|rand|read|readdir|readline|readlink|readpipe|recv|redo|ref|rename|require|reset|return|reverse|rewinddir|rindex|rmdir|scalar|seek|seekdir|select|semctl|semget|semop|send|setgrent|sethostent|setnetent|setpgrp|setpriority|setprotoent|setpwent|setservent|setsockopt|shift|shmctl|shmget|shmread|shmwrite|shutdown|sin|sleep|socket|socketpair|sort|splice|split|sprintf|sqrt|srand|stat|study|sub|substr|symlink|syscall|sysopen|sysread|sysseek|system|syswrite|tell|telldir|tie|tied|time|times|truncate|uc|ucfirst|umask|undef|unlink|unpack|unshift|untie|use|utime|values|vec|wait|waitpid|wantarray|warn|write|y)$/',
                    'missingreserved' => '/^(new)$/',
                    'flowcontrol' => '/^(if|else|elsif|while|unless|for|foreach|until|do|continue|not|or|and|eq|ne|gt|lt)$/',
                ),
                24 => 
                array (
                ),
            ),
            3 => 
            array (
                0 => 
                array (
                ),
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
                7 => -1,
                8 => -1,
                9 => 
                array (
                ),
                10 => 
                array (
                ),
                11 => 
                array (
                ),
                12 => -1,
                13 => 
                array (
                ),
                14 => 
                array (
                ),
                15 => 
                array (
                ),
                16 => 
                array (
                ),
                17 => 
                array (
                ),
                18 => 
                array (
                ),
                19 => -1,
                20 => -1,
                21 => -1,
                22 => 
                array (
                    'reserved' => '/^(abs|accept|alarm|atan2|bind|binmode|bless|caller|chdir|chmod|chomp|chop|chown|chr|chroot|close|closedir|connect|continue|cos|crypt|dbmclose|dbmopen|defined|delete|die|do|dump|each|endgrent|endhostent|endnetent|endprotoent|endpwent|endservent|eof|eval|exec|exists|exit|exp|fcntl|fileno|flock|fork|format|formline|getc|getgrent|getgrgid|getgrnam|gethostbyaddr|gethostbyname|gethostent|getlogin|getnetbyaddr|getnetbyname|getnetent|getpeername|getpgrp|getppid|getpriority|getprotobyname|getprotobynumber|getprotoent|getpwent|getpwnam|getpwuid|getservbyname|getservbyport|getservent|getsockname|getsockopt|glob|gmtime|goto|grep|hex|import|index|int|ioctl|join|keys|kill|last|lc|lcfirst|length|link|listen|local|localtime|lock|log|lstat|map|mkdir|msgctl|msgget|msgrcv|msgsnd|my|next|no|oct|open|opendir|ord|our|pack|package|pipe|pop|pos|print|printf|prototype|push|quotemeta|rand|read|readdir|readline|readlink|readpipe|recv|redo|ref|rename|require|reset|return|reverse|rewinddir|rindex|rmdir|scalar|seek|seekdir|select|semctl|semget|semop|send|setgrent|sethostent|setnetent|setpgrp|setpriority|setprotoent|setpwent|setservent|setsockopt|shift|shmctl|shmget|shmread|shmwrite|shutdown|sin|sleep|socket|socketpair|sort|splice|split|sprintf|sqrt|srand|stat|study|sub|substr|symlink|syscall|sysopen|sysread|sysseek|system|syswrite|tell|telldir|tie|tied|time|times|truncate|uc|ucfirst|umask|undef|unlink|unpack|unshift|untie|use|utime|values|vec|wait|waitpid|wantarray|warn|write|y)$/',
                    'missingreserved' => '/^(new)$/',
                    'flowcontrol' => '/^(if|else|elsif|while|unless|for|foreach|until|do|continue|not|or|and|eq|ne|gt|lt)$/',
                ),
                23 => 
                array (
                ),
            ),
            4 => 
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
            7 => 
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
            8 => 
            array (
                0 => 
                array (
                ),
            ),
            9 => 
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
            ),
            1 => 
            array (
                0 => 
                array (
                    1 => 'special',
                    2 => 'string',
                ),
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => 
                array (
                    1 => 'reserved',
                    2 => 'special',
                ),
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
                10 => 
                array (
                    1 => 'quotes',
                    2 => 'quotes',
                    3 => 'string',
                    5 => 'quotes',
                    6 => 'string',
                    8 => 'quotes',
                ),
                11 => 
                array (
                    1 => 'quotes',
                    2 => 'quotes',
                    3 => 'string',
                    5 => 'quotes',
                ),
                12 => NULL,
                13 => NULL,
                14 => NULL,
                15 => NULL,
                16 => NULL,
                17 => 
                array (
                    1 => 'brackets',
                    2 => 'var',
                    3 => 'brackets',
                ),
                18 => NULL,
                19 => NULL,
                20 => NULL,
                21 => NULL,
                22 => NULL,
                23 => NULL,
            ),
            2 => 
            array (
                0 => 
                array (
                    1 => 'special',
                    2 => 'string',
                ),
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => 
                array (
                    1 => 'reserved',
                    2 => 'special',
                ),
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
                10 => 
                array (
                    1 => 'quotes',
                    2 => 'quotes',
                    3 => 'string',
                    5 => 'quotes',
                    6 => 'string',
                    8 => 'quotes',
                ),
                11 => 
                array (
                    1 => 'quotes',
                    2 => 'quotes',
                    3 => 'string',
                    5 => 'quotes',
                ),
                12 => NULL,
                13 => 
                array (
                    1 => 'string',
                    2 => 'code',
                ),
                14 => NULL,
                15 => NULL,
                16 => NULL,
                17 => NULL,
                18 => 
                array (
                    1 => 'brackets',
                    2 => 'var',
                    3 => 'brackets',
                ),
                19 => NULL,
                20 => NULL,
                21 => NULL,
                22 => NULL,
                23 => NULL,
                24 => NULL,
            ),
            3 => 
            array (
                0 => 
                array (
                    1 => 'special',
                    2 => 'string',
                ),
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => 
                array (
                    1 => 'reserved',
                    2 => 'special',
                ),
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
                10 => 
                array (
                    1 => 'quotes',
                    2 => 'quotes',
                    3 => 'string',
                    5 => 'quotes',
                    6 => 'string',
                    8 => 'quotes',
                ),
                11 => 
                array (
                    1 => 'quotes',
                    2 => 'quotes',
                    3 => 'string',
                    5 => 'quotes',
                ),
                12 => NULL,
                13 => NULL,
                14 => NULL,
                15 => NULL,
                16 => NULL,
                17 => 
                array (
                    1 => 'brackets',
                    2 => 'var',
                    3 => 'brackets',
                ),
                18 => NULL,
                19 => NULL,
                20 => NULL,
                21 => NULL,
                22 => NULL,
                23 => NULL,
            ),
            4 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
            ),
            5 => 
            array (
                0 => NULL,
            ),
            6 => 
            array (
                0 => NULL,
            ),
            7 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
            ),
            8 => 
            array (
                0 => NULL,
            ),
            9 => 
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
                6 => false,
                7 => true,
                8 => true,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
                14 => false,
                15 => false,
                16 => false,
                17 => false,
                18 => false,
                19 => false,
                20 => false,
                21 => false,
                22 => false,
                23 => false,
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
                5 => false,
                6 => false,
                7 => true,
                8 => true,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
                14 => false,
                15 => false,
                16 => false,
                17 => false,
                18 => false,
                19 => false,
                20 => false,
                21 => false,
                22 => false,
                23 => false,
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
                7 => true,
                8 => true,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
                14 => false,
                15 => false,
                16 => false,
                17 => false,
                18 => false,
                19 => false,
                20 => false,
                21 => false,
                22 => false,
                23 => false,
                24 => false,
            ),
            3 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
                7 => true,
                8 => true,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
                14 => false,
                15 => false,
                16 => false,
                17 => false,
                18 => false,
                19 => false,
                20 => false,
                21 => false,
                22 => false,
                23 => false,
            ),
            4 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
            ),
            5 => 
            array (
                0 => false,
            ),
            6 => 
            array (
                0 => false,
            ),
            7 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
            ),
            8 => 
            array (
                0 => false,
            ),
            9 => 
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
            'reserved' => 'reserved',
            'missingreserved' => 'reserved',
            'flowcontrol' => 'reserved',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }
    
}