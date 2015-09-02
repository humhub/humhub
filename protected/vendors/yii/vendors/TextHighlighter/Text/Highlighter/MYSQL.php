<?php
/**
 * Auto-generated class. MYSQL syntax highlighting 
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
 * @version    generated from: : mysql.xml,v 1.1 2007/06/03 02:35:28 ssttoo Exp 
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */

/**
 * Auto-generated class. MYSQL syntax highlighting
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_MYSQL extends Text_Highlighter
{
    var $_language = 'mysql';

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
            -1 => '/((?i)`)|((?i)\\/\\*)|((?i)(#|--\\s).*)|((?i)[a-z_]\\w*(?=\\s*\\())|((?i)[a-z_]\\w*)|((?i)")|((?i)\\()|((?i)\')|((?i)((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+))|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\d+l?|\\b0l?\\b)|((?i)0[xX][\\da-f]+l?)/',
            0 => '//',
            1 => '//',
            2 => '/((?i)\\\\.)/',
            3 => '/((?i)`)|((?i)\\/\\*)|((?i)(#|--\\s).*)|((?i)[a-z_]\\w*(?=\\s*\\())|((?i)[a-z_]\\w*)|((?i)")|((?i)\\()|((?i)\')|((?i)((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+))|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\d+l?|\\b0l?\\b)|((?i)0[xX][\\da-f]+l?)/',
            4 => '/((?i)\\\\.)/',
        );
        $this->_counts = array (
            -1 => 
            array (
                0 => 0,
                1 => 0,
                2 => 1,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 5,
                9 => 2,
                10 => 0,
                11 => 0,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
            ),
            2 => 
            array (
                0 => 0,
            ),
            3 => 
            array (
                0 => 0,
                1 => 0,
                2 => 1,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 5,
                9 => 2,
                10 => 0,
                11 => 0,
            ),
            4 => 
            array (
                0 => 0,
            ),
        );
        $this->_delim = array (
            -1 => 
            array (
                0 => 'quotes',
                1 => 'comment',
                2 => '',
                3 => '',
                4 => '',
                5 => 'quotes',
                6 => 'brackets',
                7 => 'quotes',
                8 => '',
                9 => '',
                10 => '',
                11 => '',
            ),
            0 => 
            array (
            ),
            1 => 
            array (
            ),
            2 => 
            array (
                0 => '',
            ),
            3 => 
            array (
                0 => 'quotes',
                1 => 'comment',
                2 => '',
                3 => '',
                4 => '',
                5 => 'quotes',
                6 => 'brackets',
                7 => 'quotes',
                8 => '',
                9 => '',
                10 => '',
                11 => '',
            ),
            4 => 
            array (
                0 => '',
            ),
        );
        $this->_inner = array (
            -1 => 
            array (
                0 => 'identifier',
                1 => 'comment',
                2 => 'comment',
                3 => 'identifier',
                4 => 'identifier',
                5 => 'string',
                6 => 'code',
                7 => 'string',
                8 => 'number',
                9 => 'number',
                10 => 'number',
                11 => 'number',
            ),
            0 => 
            array (
            ),
            1 => 
            array (
            ),
            2 => 
            array (
                0 => 'special',
            ),
            3 => 
            array (
                0 => 'identifier',
                1 => 'comment',
                2 => 'comment',
                3 => 'identifier',
                4 => 'identifier',
                5 => 'string',
                6 => 'code',
                7 => 'string',
                8 => 'number',
                9 => 'number',
                10 => 'number',
                11 => 'number',
            ),
            4 => 
            array (
                0 => 'special',
            ),
        );
        $this->_end = array (
            0 => '/(?i)`/',
            1 => '/(?i)\\*\\//',
            2 => '/(?i)"/',
            3 => '/(?i)\\)/',
            4 => '/(?i)\'/',
        );
        $this->_states = array (
            -1 => 
            array (
                0 => 0,
                1 => 1,
                2 => -1,
                3 => -1,
                4 => -1,
                5 => 2,
                6 => 3,
                7 => 4,
                8 => -1,
                9 => -1,
                10 => -1,
                11 => -1,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
            ),
            2 => 
            array (
                0 => -1,
            ),
            3 => 
            array (
                0 => 0,
                1 => 1,
                2 => -1,
                3 => -1,
                4 => -1,
                5 => 2,
                6 => 3,
                7 => 4,
                8 => -1,
                9 => -1,
                10 => -1,
                11 => -1,
            ),
            4 => 
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
                3 => 
                array (
                    'function' => '/^((?i)abs|acos|adddate|ascii|asin|atan|atan2|avg|benchmark|bin|ceiling|char|coalesce|concat|conv|cos|cot|count|curdate|curtime|database|dayname|dayofmonth|dayofweek|dayofyear|decode|degrees|elt|encode|encrypt|exp|extract|field|floor|format|greatest|hex|hour|if|ifnull|insert|instr|interval|isnull|lcase|least|left|length|locate|log|log10|lower|lpad|ltrim|max|md5|mid|min|minute|mod|month|monthname|now|nullif|oct|ord|password|pi|position|pow|power|prepare|quarter|radians|rand|repeat|replace|reverse|right|round|rpad|rtrim|second|sign|sin|soundex|space|sqrt|std|stddev|strcmp|subdate|substring|sum|sysdate|tan|trim|truncate|ucase|upper|user|version|week|weekday|year)$/',
                ),
                4 => 
                array (
                    'reserved' => '/^((?i)action|add|aggregate|all|alter|after|and|as|asc|avg|avg_row_length|auto_increment|between|bigint|bit|binary|blob|bool|both|by|cascade|case|char|character|change|check|checksum|column|columns|comment|constraint|create|cross|current_date|current_time|current_timestamp|data|database|databases|date|datetime|day|day_hour|day_minute|day_second|dayofmonth|dayofweek|dayofyear|dec|decimal|default|delayed|delay_key_write|delete|desc|describe|distinct|distinctrow|double|drop|end|else|escape|escaped|enclosed|enum|explain|exists|fields|file|first|float|float4|float8|flush|foreign|from|for|full|function|global|grant|grants|group|having|heap|high_priority|hour|hour_minute|hour_second|hosts|identified|ignore|in|index|infile|inner|insert|insert_id|int|integer|interval|int1|int2|int3|int4|int8|into|if|is|isam|join|key|keys|kill|last_insert_id|leading|left|length|like|lines|limit|load|local|lock|logs|long|longblob|longtext|low_priority|max|max_rows|match|mediumblob|mediumtext|mediumint|middleint|min_rows|minute|minute_second|modify|month|monthname|myisam|natural|numeric|no|not|null|on|optimize|option|optionally|or|order|outer|outfile|pack_keys|partial|password|precision|primary|procedure|process|processlist|privileges|read|real|references|reload|regexp|rename|replace|restrict|returns|revoke|rlike|row|rows|second|select|set|show|shutdown|smallint|soname|sql_big_tables|sql_big_selects|sql_low_priority_updates|sql_log_off|sql_log_update|sql_select_limit|sql_small_result|sql_big_result|sql_warnings|straight_join|starting|status|string|table|tables|temporary|terminated|text|then|time|timestamp|tinyblob|tinytext|tinyint|trailing|to|type|use|using|unique|unlock|unsigned|update|usage|values|varchar|variables|varying|varbinary|with|write|when|where|year|year_month|zerofill)$/',
                ),
                5 => -1,
                6 => -1,
                7 => -1,
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
            ),
            0 => 
            array (
            ),
            1 => 
            array (
            ),
            2 => 
            array (
                0 => 
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
                3 => 
                array (
                    'function' => '/^((?i)abs|acos|adddate|ascii|asin|atan|atan2|avg|benchmark|bin|ceiling|char|coalesce|concat|conv|cos|cot|count|curdate|curtime|database|dayname|dayofmonth|dayofweek|dayofyear|decode|degrees|elt|encode|encrypt|exp|extract|field|floor|format|greatest|hex|hour|if|ifnull|insert|instr|interval|isnull|lcase|least|left|length|locate|log|log10|lower|lpad|ltrim|max|md5|mid|min|minute|mod|month|monthname|now|nullif|oct|ord|password|pi|position|pow|power|prepare|quarter|radians|rand|repeat|replace|reverse|right|round|rpad|rtrim|second|sign|sin|soundex|space|sqrt|std|stddev|strcmp|subdate|substring|sum|sysdate|tan|trim|truncate|ucase|upper|user|version|week|weekday|year)$/',
                ),
                4 => 
                array (
                    'reserved' => '/^((?i)action|add|aggregate|all|alter|after|and|as|asc|avg|avg_row_length|auto_increment|between|bigint|bit|binary|blob|bool|both|by|cascade|case|char|character|change|check|checksum|column|columns|comment|constraint|create|cross|current_date|current_time|current_timestamp|data|database|databases|date|datetime|day|day_hour|day_minute|day_second|dayofmonth|dayofweek|dayofyear|dec|decimal|default|delayed|delay_key_write|delete|desc|describe|distinct|distinctrow|double|drop|end|else|escape|escaped|enclosed|enum|explain|exists|fields|file|first|float|float4|float8|flush|foreign|from|for|full|function|global|grant|grants|group|having|heap|high_priority|hour|hour_minute|hour_second|hosts|identified|ignore|in|index|infile|inner|insert|insert_id|int|integer|interval|int1|int2|int3|int4|int8|into|if|is|isam|join|key|keys|kill|last_insert_id|leading|left|length|like|lines|limit|load|local|lock|logs|long|longblob|longtext|low_priority|max|max_rows|match|mediumblob|mediumtext|mediumint|middleint|min_rows|minute|minute_second|modify|month|monthname|myisam|natural|numeric|no|not|null|on|optimize|option|optionally|or|order|outer|outfile|pack_keys|partial|password|precision|primary|procedure|process|processlist|privileges|read|real|references|reload|regexp|rename|replace|restrict|returns|revoke|rlike|row|rows|second|select|set|show|shutdown|smallint|soname|sql_big_tables|sql_big_selects|sql_low_priority_updates|sql_log_off|sql_log_update|sql_select_limit|sql_small_result|sql_big_result|sql_warnings|straight_join|starting|status|string|table|tables|temporary|terminated|text|then|time|timestamp|tinyblob|tinytext|tinyint|trailing|to|type|use|using|unique|unlock|unsigned|update|usage|values|varchar|variables|varying|varbinary|with|write|when|where|year|year_month|zerofill)$/',
                ),
                5 => -1,
                6 => -1,
                7 => -1,
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
            ),
            4 => 
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
            ),
            2 => 
            array (
                0 => NULL,
            ),
            3 => 
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
            ),
            4 => 
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
                6 => false,
                7 => false,
                8 => false,
                9 => false,
                10 => false,
                11 => false,
            ),
            0 => 
            array (
            ),
            1 => 
            array (
            ),
            2 => 
            array (
                0 => false,
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
                7 => false,
                8 => false,
                9 => false,
                10 => false,
                11 => false,
            ),
            4 => 
            array (
                0 => false,
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
            'function' => 'reserved',
            'reserved' => 'reserved',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }
    
}