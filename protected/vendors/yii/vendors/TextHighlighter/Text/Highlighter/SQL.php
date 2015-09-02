<?php
/**
 * Auto-generated class. SQL syntax highlighting
 * 
 * Based on SQL-99 
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
 * @version    generated from: : sql.xml,v 1.1 2007/06/03 02:35:28 ssttoo Exp 
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */

/**
 * Auto-generated class. SQL syntax highlighting
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_SQL extends Text_Highlighter
{
    var $_language = 'sql';

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
            -1 => '/((?i)`)|((?i)\\/\\*)|((?i)(#|--\\s).*)|((?i)[a-z_]\\w*)|((?i)")|((?i)\\()|((?i)\')|((?i)((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+))|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\d+l?|\\b0l?\\b)|((?i)0[xX][\\da-f]+l?)/',
            0 => '//',
            1 => '//',
            2 => '/((?i)\\\\.)/',
            3 => '/((?i)`)|((?i)\\/\\*)|((?i)(#|--\\s).*)|((?i)[a-z_]\\w*)|((?i)")|((?i)\\()|((?i)\')|((?i)((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+))|((?i)(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\d+l?|\\b0l?\\b)|((?i)0[xX][\\da-f]+l?)/',
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
                7 => 5,
                8 => 2,
                9 => 0,
                10 => 0,
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
                7 => 5,
                8 => 2,
                9 => 0,
                10 => 0,
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
                4 => 'quotes',
                5 => 'brackets',
                6 => 'quotes',
                7 => '',
                8 => '',
                9 => '',
                10 => '',
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
                4 => 'quotes',
                5 => 'brackets',
                6 => 'quotes',
                7 => '',
                8 => '',
                9 => '',
                10 => '',
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
                4 => 'string',
                5 => 'code',
                6 => 'string',
                7 => 'number',
                8 => 'number',
                9 => 'number',
                10 => 'number',
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
                4 => 'string',
                5 => 'code',
                6 => 'string',
                7 => 'number',
                8 => 'number',
                9 => 'number',
                10 => 'number',
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
                4 => 2,
                5 => 3,
                6 => 4,
                7 => -1,
                8 => -1,
                9 => -1,
                10 => -1,
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
                4 => 2,
                5 => 3,
                6 => 4,
                7 => -1,
                8 => -1,
                9 => -1,
                10 => -1,
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
                    'reserved' => '/^((?i)absolute|action|add|admin|after|aggregate|alias|all|allocate|alter|and|any|are|array|as|asc|assertion|at|authorization|before|begin|binary|bit|blob|boolean|both|breadth|by|call|cascade|cascaded|case|cast|catalog|char|character|check|class|clob|close|collate|collation|column|commit|completion|connect|connection|constraint|constraints|constructor|continue|corresponding|create|cross|cube|current|current_date|current_path|current_role|current_time|current_timestamp|current_user|cursor|cycle|data|date|day|deallocate|dec|decimal|declare|default|deferrable|deferred|delete|depth|deref|desc|describe|descriptor|destroy|destructor|deterministic|diagnostics|dictionary|disconnect|distinct|domain|double|drop|dynamic|each|else|end|end-exec|equals|escape|every|except|exception|exec|execute|external|false|fetch|first|float|for|foreign|found|free|from|full|function|general|get|global|go|goto|grant|group|grouping|having|host|hour|identity|ignore|immediate|in|indicator|initialize|initially|inner|inout|input|insert|int|integer|intersect|interval|into|is|isolation|iterate|join|key|language|large|last|lateral|leading|left|less|level|like|limit|local|localtime|localtimestamp|locator|map|match|minute|modifies|modify|module|month|names|national|natural|nchar|nclob|new|next|no|none|not|null|numeric|object|of|off|old|on|only|open|operation|option|or|order|ordinality|out|outer|output|pad|parameter|parameters|partial|path|postfix|precision|prefix|preorder|prepare|preserve|primary|prior|privileges|procedure|public|read|reads|real|recursive|ref|references|referencing|relative|restrict|result|return|returns|revoke|right|role|rollback|rollup|routine|row|rows|savepoint|schema|scope|scroll|search|second|section|select|sequence|session|session_user|set|sets|size|smallint|some|space|specific|specifictype|sql|sqlexception|sqlstate|sqlwarning|start|state|statement|static|structure|system_user|table|temporary|terminate|than|then|time|timestamp|timezone_hour|timezone_minute|to|trailing|transaction|translation|treat|trigger|true|under|union|unique|unknown|unnest|update|usage|user|using|value|values|varchar|variable|varying|view|when|whenever|where|with|without|work|write|year|zone)$/',
                    'keyword' => '/^((?i)abs|ada|asensitive|assignment|asymmetric|atomic|avg|between|bitvar|bit_length|c|called|cardinality|catalog_name|chain|character_length|character_set_catalog|character_set_name|character_set_schema|char_length|checked|class_origin|coalesce|cobol|collation_catalog|collation_name|collation_schema|column_name|command_function|command_function_code|committed|condition_number|connection_name|constraint_catalog|constraint_name|constraint_schema|contains|convert|count|cursor_name|datetime_interval_code|datetime_interval_precision|defined|definer|dispatch|dynamic_function|dynamic_function_code|existing|exists|extract|final|fortran|g|generated|granted|hierarchy|hold|implementation|infix|insensitive|instance|instantiable|invoker|k|key_member|key_type|length|lower|m|max|message_length|message_octet_length|message_text|method|min|mod|more|mumps|name|nullable|nullif|number|octet_length|options|overlaps|overlay|overriding|parameter_mode|parameter_name|parameter_ordinal_position|parameter_specific_catalog|parameter_specific_name|parameter_specific_schema|pascal|pli|position|repeatable|returned_length|returned_octet_length|returned_sqlstate|routine_catalog|routine_name|routine_schema|row_count|scale|schema_name|security|self|sensitive|serializable|server_name|similar|simple|source|specific_name|style|subclass_origin|sublist|substring|sum|symmetric|system|table_name|transactions_committed|transactions_rolled_back|transaction_active|transform|transforms|translate|trigger_catalog|trigger_name|trigger_schema|trim|type|uncommitted|unnamed|upper|user_defined_type_catalog|user_defined_type_name|user_defined_type_schema)$/',
                ),
                4 => -1,
                5 => -1,
                6 => -1,
                7 => 
                array (
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
                    'reserved' => '/^((?i)absolute|action|add|admin|after|aggregate|alias|all|allocate|alter|and|any|are|array|as|asc|assertion|at|authorization|before|begin|binary|bit|blob|boolean|both|breadth|by|call|cascade|cascaded|case|cast|catalog|char|character|check|class|clob|close|collate|collation|column|commit|completion|connect|connection|constraint|constraints|constructor|continue|corresponding|create|cross|cube|current|current_date|current_path|current_role|current_time|current_timestamp|current_user|cursor|cycle|data|date|day|deallocate|dec|decimal|declare|default|deferrable|deferred|delete|depth|deref|desc|describe|descriptor|destroy|destructor|deterministic|diagnostics|dictionary|disconnect|distinct|domain|double|drop|dynamic|each|else|end|end-exec|equals|escape|every|except|exception|exec|execute|external|false|fetch|first|float|for|foreign|found|free|from|full|function|general|get|global|go|goto|grant|group|grouping|having|host|hour|identity|ignore|immediate|in|indicator|initialize|initially|inner|inout|input|insert|int|integer|intersect|interval|into|is|isolation|iterate|join|key|language|large|last|lateral|leading|left|less|level|like|limit|local|localtime|localtimestamp|locator|map|match|minute|modifies|modify|module|month|names|national|natural|nchar|nclob|new|next|no|none|not|null|numeric|object|of|off|old|on|only|open|operation|option|or|order|ordinality|out|outer|output|pad|parameter|parameters|partial|path|postfix|precision|prefix|preorder|prepare|preserve|primary|prior|privileges|procedure|public|read|reads|real|recursive|ref|references|referencing|relative|restrict|result|return|returns|revoke|right|role|rollback|rollup|routine|row|rows|savepoint|schema|scope|scroll|search|second|section|select|sequence|session|session_user|set|sets|size|smallint|some|space|specific|specifictype|sql|sqlexception|sqlstate|sqlwarning|start|state|statement|static|structure|system_user|table|temporary|terminate|than|then|time|timestamp|timezone_hour|timezone_minute|to|trailing|transaction|translation|treat|trigger|true|under|union|unique|unknown|unnest|update|usage|user|using|value|values|varchar|variable|varying|view|when|whenever|where|with|without|work|write|year|zone)$/',
                    'keyword' => '/^((?i)abs|ada|asensitive|assignment|asymmetric|atomic|avg|between|bitvar|bit_length|c|called|cardinality|catalog_name|chain|character_length|character_set_catalog|character_set_name|character_set_schema|char_length|checked|class_origin|coalesce|cobol|collation_catalog|collation_name|collation_schema|column_name|command_function|command_function_code|committed|condition_number|connection_name|constraint_catalog|constraint_name|constraint_schema|contains|convert|count|cursor_name|datetime_interval_code|datetime_interval_precision|defined|definer|dispatch|dynamic_function|dynamic_function_code|existing|exists|extract|final|fortran|g|generated|granted|hierarchy|hold|implementation|infix|insensitive|instance|instantiable|invoker|k|key_member|key_type|length|lower|m|max|message_length|message_octet_length|message_text|method|min|mod|more|mumps|name|nullable|nullif|number|octet_length|options|overlaps|overlay|overriding|parameter_mode|parameter_name|parameter_ordinal_position|parameter_specific_catalog|parameter_specific_name|parameter_specific_schema|pascal|pli|position|repeatable|returned_length|returned_octet_length|returned_sqlstate|routine_catalog|routine_name|routine_schema|row_count|scale|schema_name|security|self|sensitive|serializable|server_name|similar|simple|source|specific_name|style|subclass_origin|sublist|substring|sum|symmetric|system|table_name|transactions_committed|transactions_rolled_back|transaction_active|transform|transforms|translate|trigger_catalog|trigger_name|trigger_schema|trim|type|uncommitted|unnamed|upper|user_defined_type_catalog|user_defined_type_name|user_defined_type_schema)$/',
                ),
                4 => -1,
                5 => -1,
                6 => -1,
                7 => 
                array (
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
            ),
            4 => 
            array (
                0 => false,
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
            'reserved' => 'reserved',
            'keyword' => 'var',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }
    
}