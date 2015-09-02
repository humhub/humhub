<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
* Syntax highlighter class generator
*
* To simplify the process of creating new syntax highlighters
* for different languages, {@link Text_Highlighter_Generator} class is
* provided. It takes highlighting rules from XML file and generates
* a code of a class inherited from {@link Text_Highlighter}.
*
* PHP versions 4 and 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license/3_0.txt.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @category   Text
* @package    Text_Highlighter
* @author     Andrey Demenev <demenev@gmail.com>
* @copyright  2004-2006 Andrey Demenev
* @license    http://www.php.net/license/3_0.txt  PHP License
* @version    CVS: $Id: Generator.php,v 1.1 2007/06/03 02:36:35 ssttoo Exp $
* @link       http://pear.php.net/package/Text_Highlighter
*/

// {{{ error codes

define ('TEXT_HIGHLIGHTER_EMPTY_RE',          1);
define ('TEXT_HIGHLIGHTER_INVALID_RE',        2);
define ('TEXT_HIGHLIGHTER_EMPTY_OR_MISSING',  3);
define ('TEXT_HIGHLIGHTER_EMPTY',             4);
define ('TEXT_HIGHLIGHTER_REGION_REGION',     5);
define ('TEXT_HIGHLIGHTER_REGION_BLOCK',      6);
define ('TEXT_HIGHLIGHTER_BLOCK_REGION',      7);
define ('TEXT_HIGHLIGHTER_KEYWORD_BLOCK',     8);
define ('TEXT_HIGHLIGHTER_KEYWORD_INHERITS',  9);
define ('TEXT_HIGHLIGHTER_PARSE',            10);
define ('TEXT_HIGHLIGHTER_FILE_WRITE',       11);
define ('TEXT_HIGHLIGHTER_FILE_READ',        12);
// }}}

/**
* Syntax highliter class generator class
*
* This class is used to generate PHP classes
* from XML files with highlighting rules
*
* Usage example
* <code>
*require_once 'Text/Highlighter/Generator.php';
*$generator =& new Text_Highlighter_Generator('php.xml');
*$generator->generate();
*$generator->saveCode('PHP.php');
* </code>
*
* A command line script <b>generate</b> is provided for
* class generation (installs in scripts/Text/Highlighter).
*
* @author     Andrey Demenev <demenev@gmail.com>
* @copyright  2004-2006 Andrey Demenev
* @license    http://www.php.net/license/3_0.txt  PHP License
* @version    Release: 0.7.1
* @link       http://pear.php.net/package/Text_Highlighter
*/

class Text_Highlighter_Generator extends  XML_Parser
{
    // {{{ properties
    /**
    * Whether to do case folding.
    * We have to declare it here, because XML_Parser
    * sets case folding in constructor
    *
    * @var  boolean
    */
    var $folding = false;

    /**
    * Holds name of file with highlighting rules
    *
    * @var string
    * @access private
    */
    var $_syntaxFile;

    /**
    * Current element being processed
    *
    * @var array
    * @access private
    */
    var $_element;

    /**
    * List of regions
    *
    * @var array
    * @access private
    */
    var $_regions = array();

    /**
    * List of blocks
    *
    * @var array
    * @access private
    */
    var $_blocks = array();

    /**
    * List of keyword groups
    *
    * @var array
    * @access private
    */
    var $_keywords = array();

    /**
    * List of authors
    *
    * @var array
    * @access private
    */
    var $_authors = array();

    /**
    * Name of language
    *
    * @var string
    * @access public
    */
    var $language = '';

    /**
    * Generated code
    *
    * @var string
    * @access private
    */
    var $_code = '';

    /**
    * Default class
    *
    * @var string
    * @access private
    */
    var $_defClass = 'default';

    /**
    * Comment
    *
    * @var string
    * @access private
    */
    var $_comment = '';

    /**
    * Flag for comment processing
    *
    * @var boolean
    * @access private
    */
    var $_inComment = false;

    /**
    * Sorting order of current block/region
    *
    * @var integer
    * @access private
    */
    var $_blockOrder = 0;

    /**
    * Generation errors
    *
    * @var array
    * @access private
    */
    var $_errors;

    // }}}
    // {{{ constructor

    /**
    * Constructor
    *
    * @param string $syntaxFile Name of XML file
    * with syntax highlighting rules
    *
    * @access public
    */

    function __construct($syntaxFile = '')
    {
        XML_Parser::XML_Parser(null, 'func');
        $this->_errors = array();
        $this->_declareErrorMessages();
        if ($syntaxFile) {
            $this->setInputFile($syntaxFile);
        }
    }

    // }}}
    // {{{ _formatError

    /**
    * Format error message
    *
    * @param integer $code error code
    * @param string $params parameters
    * @param string $fileName file name
    * @param integer $lineNo line number
    * @return  array
    * @access  public
    */
    function _formatError($code, $params, $fileName, $lineNo)
    {
        $template = $this->_templates[$code];
        $ret = call_user_func_array('sprintf', array_merge(array($template), $params));
        if ($fileName) {
            $ret = '[' . $fileName . '] ' . $ret;
        }
        if ($lineNo) {
            $ret .= ' (line ' . $lineNo . ')';
        }
        return $ret;
    }

    // }}}
    // {{{ declareErrorMessages

    /**
    * Set up error message templates
    *
    * @access  private
    */
    function _declareErrorMessages()
    {
        $this->_templates = array (
        TEXT_HIGHLIGHTER_EMPTY_RE => 'Empty regular expression',
        TEXT_HIGHLIGHTER_INVALID_RE => 'Invalid regular expression : %s',
        TEXT_HIGHLIGHTER_EMPTY_OR_MISSING => 'Empty or missing %s',
        TEXT_HIGHLIGHTER_EMPTY  => 'Empty %s',
        TEXT_HIGHLIGHTER_REGION_REGION => 'Region %s refers undefined region %s',
        TEXT_HIGHLIGHTER_REGION_BLOCK => 'Region %s refers undefined block %s',
        TEXT_HIGHLIGHTER_BLOCK_REGION => 'Block %s refers undefined region %s',
        TEXT_HIGHLIGHTER_KEYWORD_BLOCK => 'Keyword group %s refers undefined block %s',
        TEXT_HIGHLIGHTER_KEYWORD_INHERITS => 'Keyword group %s inherits undefined block %s',
        TEXT_HIGHLIGHTER_PARSE => '%s',
        TEXT_HIGHLIGHTER_FILE_WRITE => 'Error writing file %s',
        TEXT_HIGHLIGHTER_FILE_READ => '%s'
        );
    }

    // }}}
    // {{{ setInputFile

    /**
    * Sets the input xml file to be parsed
    *
    * @param    string      Filename (full path)
    * @return   boolean
    * @access   public
    */
    function setInputFile($file)
    {
        $this->_syntaxFile = $file;
        $ret = parent::setInputFile($file);
        if (PEAR::isError($ret)) {
            $this->_error(TEXT_HIGHLIGHTER_FILE_READ, $ret->message);
            return false;
        }
        return true;
    }

    // }}}
    // {{{ generate

    /**
    * Generates class code
    *
    * @access public
    */

    function generate()
    {
        $this->_regions    = array();
        $this->_blocks     = array();
        $this->_keywords   = array();
        $this->language    = '';
        $this->_code       = '';
        $this->_defClass   = 'default';
        $this->_comment    = '';
        $this->_inComment  = false;
        $this->_authors    = array();
        $this->_blockOrder = 0;
        $this->_errors   = array();

        $ret = $this->parse();
        if (PEAR::isError($ret)) {
            $this->_error(TEXT_HIGHLIGHTER_PARSE, $ret->message);
            return false;
        }
        return true;
    }

    // }}}
    // {{{ getCode

    /**
    * Returns generated code as a string.
    *
    * @return string Generated code
    * @access public
    */

    function getCode()
    {
        return $this->_code;
    }

    // }}}
    // {{{ saveCode

    /**
    * Saves generated class to file. Note that {@link Text_Highlighter::factory()}
    * assumes that filename is uppercase (SQL.php, DTD.php, etc), and file
    * is located in Text/Highlighter
    *
    * @param string $filename Name of file to write the code to
    * @return boolean true on success, false on failure
    * @access public
    */

    function saveCode($filename)
    {
        $f = @fopen($filename, 'wb');
        if (!$f) {
            $this->_error(TEXT_HIGHLIGHTER_FILE_WRITE, array('outfile'=>$filename));
            return false;
        }
        fwrite ($f, $this->_code);
        fclose($f);
        return true;
    }

    // }}}
    // {{{ hasErrors

    /**
    * Reports if there were errors
    *
    * @return boolean
    * @access public
    */

    function hasErrors()
    {
        return count($this->_errors) > 0;
    }

    // }}}
    // {{{ getErrors

    /**
    * Returns errors
    *
    * @return array
    * @access public
    */

    function getErrors()
    {
        return $this->_errors;
    }

    // }}}
    // {{{ _sortBlocks

    /**
    * Sorts blocks
    *
    * @access private
    */

    function _sortBlocks($b1, $b2) {
        return $b1['order'] - $b2['order'];
    }

    // }}}
    // {{{ _sortLookFor
    /**
    * Sort 'look for' list
    * @return int
    * @param string $b1
    * @param string $b2
    */
    function _sortLookFor($b1, $b2) {
        $o1 = isset($this->_blocks[$b1]) ? $this->_blocks[$b1]['order'] : $this->_regions[$b1]['order'];
        $o2 = isset($this->_blocks[$b2]) ? $this->_blocks[$b2]['order'] : $this->_regions[$b2]['order'];
        return $o1 - $o2;
    }

    // }}}
    // {{{ _makeRE

    /**
    * Adds delimiters and modifiers to regular expression if necessary
    *
    * @param string $text Original RE
    * @return string Final RE
    * @access private
    */
    function _makeRE($text, $case = false)
    {
        if (!strlen($text)) {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_RE);
        }
        if (!strlen($text) || $text{0} != '/') {
            $text = '/' . $text . '/';
        }
        if (!$case) {
            $text .= 'i';
        }
        $php_errormsg = '';
        @preg_match($text, '');
        if ($php_errormsg) {
            $this->_error(TEXT_HIGHLIGHTER_INVALID_RE, $php_errormsg);
        }
        preg_match ('#^/(.+)/(.*)$#', $text, $m);
        if (@$m[2]) {
            $text = '(?' . $m[2] . ')' . $m[1];
        } else {
            $text = $m[1];
        }
        return $text;
    }

    // }}}
    // {{{ _exportArray

    /**
    * Exports array as PHP code
    *
    * @param array $array
    * @return string Code
    * @access private
    */
    function _exportArray($array)
    {
        $array = var_export($array, true);
        return trim(preg_replace('~^(\s*)~m','        \1\1',$array));
    }

    // }}}
    // {{{ _countSubpatterns
    /**
    * Find number of capturing suppaterns in regular expression
    * @return int
    * @param string $re Regular expression (without delimiters)
    */
    function _countSubpatterns($re)
    {
        preg_match_all('/' . $re . '/', '', $m);
        return count($m)-1;
    }

    // }}}

    /**#@+
    * @access private
    * @param resource $xp      XML parser resource
    * @param string   $elem    XML element name
    * @param array    $attribs XML element attributes
    */

    // {{{ xmltag_Default

    /**
    * start handler for <default> element
    */
    function xmltag_Default($xp, $elem, $attribs)
    {
        $this->_aliasAttributes($attribs);
        if (!isset($attribs['innerGroup']) || $attribs['innerGroup'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'innerGroup');
        }
        $this->_defClass = @$attribs['innerGroup'];
    }

    // }}}
    // {{{ xmltag_Region

    /**
    * start handler for <region> element
    */
    function xmltag_Region($xp, $elem, $attribs)
    {
        $this->_aliasAttributes($attribs);
        if (!isset($attribs['name']) || $attribs['name'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'region name');
        }
        if (!isset($attribs['innerGroup']) || $attribs['innerGroup'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'innerGroup');
        }
        $this->_element = array('name' => $attribs['name']);
        $this->_element['line'] = xml_get_current_line_number($this->parser);
        if (isset($attribs['case'])) {
            $this->_element['case'] = $attribs['case'] == 'yes';
        } else {
            $this->_element['case'] = $this->_case;
        }
        $this->_element['innerGroup'] = $attribs['innerGroup'];
        $this->_element['delimGroup'] = isset($attribs['delimGroup']) ?
        $attribs['delimGroup'] :
        $attribs['innerGroup'];
        $this->_element['start'] = $this->_makeRE(@$attribs['start'], $this->_element['case']);
        $this->_element['end'] = $this->_makeRE(@$attribs['end'], $this->_element['case']);
        $this->_element['contained'] = @$attribs['contained'] == 'yes';
        $this->_element['never-contained'] = @$attribs['never-contained'] == 'yes';
        $this->_element['remember'] = @$attribs['remember'] == 'yes';
        if (isset($attribs['startBOL']) && $attribs['startBOL'] == 'yes') {
            $this->_element['startBOL'] = true;
        }
        if (isset($attribs['endBOL']) && $attribs['endBOL'] == 'yes') {
            $this->_element['endBOL'] = true;
        }
        if (isset($attribs['neverAfter'])) {
            $this->_element['neverafter'] = $this->_makeRE($attribs['neverAfter']);
        }
    }

    // }}}
    // {{{ xmltag_Block

    /**
    * start handler for <block> element
    */
    function xmltag_Block($xp, $elem, $attribs)
    {
        $this->_aliasAttributes($attribs);
        if (!isset($attribs['name']) || $attribs['name'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'block name');
        }
        if (isset($attribs['innerGroup']) && $attribs['innerGroup'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY, 'innerGroup');
        }
        $this->_element = array('name' => $attribs['name']);
        $this->_element['line'] = xml_get_current_line_number($this->parser);
        if (isset($attribs['case'])) {
            $this->_element['case'] = $attribs['case'] == 'yes';
        } else {
            $this->_element['case'] = $this->_case;
        }
        if (isset($attribs['innerGroup'])) {
            $this->_element['innerGroup'] = @$attribs['innerGroup'];
        }
        $this->_element['match'] = $this->_makeRE($attribs['match'], $this->_element['case']);
        $this->_element['contained'] = @$attribs['contained'] == 'yes';
        $this->_element['multiline'] = @$attribs['multiline'] == 'yes';
        if (isset($attribs['BOL']) && $attribs['BOL'] == 'yes') {
            $this->_element['BOL'] = true;
        }
        if (isset($attribs['neverAfter'])) {
            $this->_element['neverafter'] = $this->_makeRE($attribs['neverAfter']);
        }
    }

    // }}}
    // {{{ cdataHandler

    /**
    * Character data handler. Used for comment
    */
    function cdataHandler($xp, $cdata)
    {
        if ($this->_inComment) {
            $this->_comment .= $cdata;
        }
    }

    // }}}
    // {{{ xmltag_Comment

    /**
    * start handler for <comment> element
    */
    function xmltag_Comment($xp, $elem, $attribs)
    {
        $this->_comment = '';
        $this->_inComment = true;
    }

    // }}}
    // {{{ xmltag_PartGroup

    /**
    * start handler for <partgroup> element
    */
    function xmltag_PartGroup($xp, $elem, $attribs)
    {
        $this->_aliasAttributes($attribs);
        if (!isset($attribs['innerGroup']) || $attribs['innerGroup'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'innerGroup');
        }
        $this->_element['partClass'][$attribs['index']] = @$attribs['innerGroup'];
    }

    // }}}
    // {{{ xmltag_PartClass

    /**
    * start handler for <partclass> element
    */
    function xmltag_PartClass($xp, $elem, $attribs)
    {
        $this->xmltag_PartGroup($xp, $elem, $attribs);
    }

    // }}}
    // {{{ xmltag_Keywords

    /**
    * start handler for <keywords> element
    */
    function xmltag_Keywords($xp, $elem, $attribs)
    {
        $this->_aliasAttributes($attribs);
        if (!isset($attribs['name']) || $attribs['name'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'keyword group name');
        }
        if (!isset($attribs['innerGroup']) || $attribs['innerGroup'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'innerGroup');
        }
        if (!isset($attribs['inherits']) || $attribs['inherits'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'inherits');
        }
        $this->_element = array('name'=>@$attribs['name']);
        $this->_element['line'] = xml_get_current_line_number($this->parser);
        $this->_element['innerGroup'] = @$attribs['innerGroup'];
        if (isset($attribs['case'])) {
            $this->_element['case'] = $attribs['case'] == 'yes';
        } else {
            $this->_element['case'] = $this->_case;
        }
        $this->_element['inherits'] = @$attribs['inherits'];
        if (isset($attribs['otherwise'])) {
            $this->_element['otherwise'] = $attribs['otherwise'];
        }
        if (isset($attribs['ifdef'])) {
            $this->_element['ifdef'] = $attribs['ifdef'];
        }
        if (isset($attribs['ifndef'])) {
            $this->_element['ifndef'] = $attribs['ifndef'];
        }
    }

    // }}}
    // {{{ xmltag_Keyword

    /**
    * start handler for <keyword> element
    */
    function xmltag_Keyword($xp, $elem, $attribs)
    {
        if (!isset($attribs['match']) || $attribs['match'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'match');
        }
        $keyword = @$attribs['match'];
        if (!$this->_element['case']) {
            $keyword = strtolower($keyword);
        }
        $this->_element['match'][$keyword] = true;
    }

    // }}}
    // {{{ xmltag_Contains

    /**
    * start handler for <contains> element
    */
    function xmltag_Contains($xp, $elem, $attribs)
    {
        $this->_element['contains-all'] = @$attribs['all'] == 'yes';
        if (isset($attribs['region'])) {
            $this->_element['contains']['region'][$attribs['region']] =
            xml_get_current_line_number($this->parser);
        }
        if (isset($attribs['block'])) {
            $this->_element['contains']['block'][$attribs['block']] =
            xml_get_current_line_number($this->parser);
        }
    }

    // }}}
    // {{{ xmltag_But

    /**
    * start handler for <but> element
    */
    function xmltag_But($xp, $elem, $attribs)
    {
        if (isset($attribs['region'])) {
            $this->_element['not-contains']['region'][$attribs['region']] = true;
        }
        if (isset($attribs['block'])) {
            $this->_element['not-contains']['block'][$attribs['block']] = true;
        }
    }

    // }}}
    // {{{ xmltag_Onlyin

    /**
    * start handler for <onlyin> element
    */
    function xmltag_Onlyin($xp, $elem, $attribs)
    {
        if (!isset($attribs['region']) || $attribs['region'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'region');
        }
        $this->_element['onlyin'][$attribs['region']] = xml_get_current_line_number($this->parser);
    }

    // }}}
    // {{{ xmltag_Author

    /**
    * start handler for <author> element
    */
    function xmltag_Author($xp, $elem, $attribs)
    {
        if (!isset($attribs['name']) || $attribs['name'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'author name');
        }
        $this->_authors[] = array(
        'name'  => @$attribs['name'],
        'email' => (string)@$attribs['email']
        );
    }

    // }}}
    // {{{ xmltag_Highlight

    /**
    * start handler for <highlight> element
    */
    function xmltag_Highlight($xp, $elem, $attribs)
    {
        if (!isset($attribs['lang']) || $attribs['lang'] === '') {
            $this->_error(TEXT_HIGHLIGHTER_EMPTY_OR_MISSING, 'language name');
        }
        $this->_code = '';
        $this->language = strtoupper(@$attribs['lang']);
        $this->_case = @$attribs['case'] == 'yes';
    }

    // }}}

    /**#@-*/

    // {{{ _error

    /**
    * Add an error message
    *
    * @param integer $code Error code
    * @param mixed   $message Error message or array with error message parameters
    * @param integer $lineNo Source code line number
    * @access private
    */
    function _error($code, $params = array(), $lineNo = 0)
    {
        if (!$lineNo && !empty($this->parser)) {
            $lineNo = xml_get_current_line_number($this->parser);
        }
        $this->_errors[] = $this->_formatError($code, $params, $this->_syntaxFile, $lineNo);
    }

    // }}}
    // {{{ _aliasAttributes

    /**
    * BC trick
    *
    * @param array $attrs attributes
    */
    function _aliasAttributes(&$attrs)
    {
        if (isset($attrs['innerClass']) && !isset($attrs['innerGroup'])) {
            $attrs['innerGroup'] = $attrs['innerClass'];
        }
        if (isset($attrs['delimClass']) && !isset($attrs['delimGroup'])) {
            $attrs['delimGroup'] = $attrs['delimClass'];
        }
        if (isset($attrs['partClass']) && !isset($attrs['partGroup'])) {
            $attrs['partGroup'] = $attrs['partClass'];
        }
    }

    // }}}

    /**#@+
    * @access private
    * @param resource $xp      XML parser resource
    * @param string   $elem    XML element name
    */

    // {{{ xmltag_Comment_

    /**
    * end handler for <comment> element
    */
    function xmltag_Comment_($xp, $elem)
    {
        $this->_inComment = false;
    }

    // }}}
    // {{{ xmltag_Region_

    /**
    * end handler for <region> element
    */
    function xmltag_Region_($xp, $elem)
    {
        $this->_element['type'] = 'region';
        $this->_element['order'] = $this->_blockOrder ++;
        $this->_regions[$this->_element['name']] = $this->_element;
    }

    // }}}
    // {{{ xmltag_Keywords_

    /**
    * end handler for <keywords> element
    */
    function xmltag_Keywords_($xp, $elem)
    {
        $this->_keywords[$this->_element['name']] = $this->_element;
    }

    // }}}
    // {{{ xmltag_Block_

    /**
    * end handler for <block> element
    */
    function xmltag_Block_($xp, $elem)
    {
        $this->_element['type'] = 'block';
        $this->_element['order'] = $this->_blockOrder ++;
        $this->_blocks[$this->_element['name']] = $this->_element;
    }

    // }}}
    // {{{ xmltag_Highlight_

    /**
    * end handler for <highlight> element
    */
    function xmltag_Highlight_($xp, $elem)
    {
        $conditions = array();
        $toplevel = array();
        foreach ($this->_blocks as $i => $current) {
            if (!$current['contained'] && !isset($current['onlyin'])) {
                $toplevel[] = $i;
            }
            foreach ((array)@$current['onlyin'] as $region => $lineNo) {
                if (!isset($this->_regions[$region])) {
                    $this->_error(TEXT_HIGHLIGHTER_BLOCK_REGION,
                    array(
                    'block' => $current['name'],
                    'region' => $region
                    ));
                }
            }
        }
        foreach ($this->_regions as $i=>$current) {
            if (!$current['contained'] && !isset($current['onlyin'])) {
                $toplevel[] = $i;
            }
            foreach ((array)@$current['contains']['region'] as $region => $lineNo) {
                if (!isset($this->_regions[$region])) {
                    $this->_error(TEXT_HIGHLIGHTER_REGION_REGION,
                    array(
                    'region1' => $current['name'],
                    'region2' => $region
                    ));
                }
            }
            foreach ((array)@$current['contains']['block'] as $region => $lineNo) {
                if (!isset($this->_blocks[$region])) {
                    $this->_error(TEXT_HIGHLIGHTER_REGION_BLOCK,
                    array(
                    'block' => $current['name'],
                    'region' => $region
                    ));
                }
            }
            foreach ((array)@$current['onlyin'] as $region => $lineNo) {
                if (!isset($this->_regions[$region])) {
                    $this->_error(TEXT_HIGHLIGHTER_REGION_REGION,
                    array(
                    'region1' => $current['name'],
                    'region2' => $region
                    ));
                }
            }
            foreach ($this->_regions as $j => $region) {
                if (isset($region['onlyin'])) {
                    $suits = isset($region['onlyin'][$current['name']]);
                } elseif (isset($current['not-contains']['region'][$region['name']])) {
                    $suits = false;
                } elseif (isset($current['contains']['region'][$region['name']])) {
                    $suits = true;
                } else {
                    $suits = @$current['contains-all'] && @!$region['never-contained'];
                }
                if ($suits) {
                    $this->_regions[$i]['lookfor'][] = $j;
                }
            }
            foreach ($this->_blocks as $j=>$region) {
                if (isset($region['onlyin'])) {
                    $suits = isset($region['onlyin'][$current['name']]);
                } elseif (isset($current['not-contains']['block'][$region['name']])) {
                    $suits = false;
                } elseif (isset($current['contains']['block'][$region['name']])) {
                    $suits = true;
                } else {
                    $suits = @$current['contains-all'] && @!$region['never-contained'];
                }
                if ($suits) {
                    $this->_regions[$i]['lookfor'][] = $j;
                }
            }
        }
        foreach ($this->_blocks as $i=>$current) {
            unset ($this->_blocks[$i]['never-contained']);
            unset ($this->_blocks[$i]['contained']);
            unset ($this->_blocks[$i]['contains-all']);
            unset ($this->_blocks[$i]['contains']);
            unset ($this->_blocks[$i]['onlyin']);
            unset ($this->_blocks[$i]['line']);
        }

        foreach ($this->_regions as $i=>$current) {
            unset ($this->_regions[$i]['never-contained']);
            unset ($this->_regions[$i]['contained']);
            unset ($this->_regions[$i]['contains-all']);
            unset ($this->_regions[$i]['contains']);
            unset ($this->_regions[$i]['onlyin']);
            unset ($this->_regions[$i]['line']);
        }

        foreach ($this->_keywords as $name => $keyword) {
            if (isset($keyword['ifdef'])) {
                $conditions[$keyword['ifdef']][] = array($name, true);
            }
            if (isset($keyword['ifndef'])) {
                $conditions[$keyword['ifndef']][] = array($name, false);
            }
            unset($this->_keywords[$name]['line']);
            if (!isset($this->_blocks[$keyword['inherits']])) {
                $this->_error(TEXT_HIGHLIGHTER_KEYWORD_INHERITS,
                array(
                'keyword' => $keyword['name'],
                'block' => $keyword['inherits']
                ));
            }
            if (isset($keyword['otherwise']) && !isset($this->_blocks[$keyword['otherwise']]) ) {
                $this->_error(TEXT_HIGHLIGHTER_KEYWORD_BLOCK,
                array(
                'keyword' => $keyword['name'],
                'block' => $keyword['inherits']
                ));
            }
        }

        $syntax=array(
        'keywords'   => $this->_keywords,
        'blocks'     => array_merge($this->_blocks, $this->_regions),
        'toplevel'   => $toplevel,
        );
        uasort($syntax['blocks'], array(&$this, '_sortBlocks'));
        foreach ($syntax['blocks'] as $name => $block) {
            if ($block['type'] == 'block') {
                continue;
            }
            if (is_array(@$syntax['blocks'][$name]['lookfor'])) {
                usort($syntax['blocks'][$name]['lookfor'], array(&$this, '_sortLookFor'));
            }
        }
        usort($syntax['toplevel'], array(&$this, '_sortLookFor'));
        $syntax['case'] = $this->_case;
        $this->_code = <<<CODE
<?php
/**
 * Auto-generated class. {$this->language} syntax highlighting
CODE;

        if ($this->_comment) {
            $comment = preg_replace('~^~m',' * ',$this->_comment);
            $this->_code .= "\n * \n" . $comment;
        }

        $this->_code .= <<<CODE
 
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
 * @version    generated from: $this->_syntaxFile

CODE;

        foreach ($this->_authors as $author) {
            $this->_code .= ' * @author ' . $author['name'];
            if ($author['email']) {
                $this->_code .= ' <' . $author['email'] . '>';
            }
            $this->_code .= "\n";
        }

        $this->_code .= <<<CODE
 *
 */

/**
 * Auto-generated class. {$this->language} syntax highlighting
 *

CODE;
        foreach ($this->_authors as $author) {
            $this->_code .= ' * @author ' . $author['name'];
            if ($author['email']) {
                $this->_code .= ' <' . $author['email']. '>';
            }
            $this->_code .= "\n";
        }


        $this->_code .= <<<CODE
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_{$this->language} extends Text_Highlighter
{
    
CODE;
        $this->_code .= 'var $_language = \'' . strtolower($this->language) . "';\n\n";
        $array = var_export($syntax, true);
        $array = trim(preg_replace('~^(\s*)~m','        \1\1',$array));
        //        \$this->_syntax = $array;
        $this->_code .= <<<CODE

    /**
     *  Constructor
     *
     * @param array  \$options
     * @access public
     */
    function __construct(\$options=array())
    {

CODE;
        $this->_code .= <<<CODE

        \$this->_options = \$options;
CODE;
        $states = array();
        $i = 0;
        foreach ($syntax['blocks'] as $name => $block) {
            if ($block['type'] == 'region') {
                $states[$name] = $i++;
            }
        }
        $regs = array();
        $counts = array();
        $delim = array();
        $inner = array();
        $end = array();
        $stat = array();
        $keywords = array();
        $parts = array();
        $kwmap = array();
        $subst = array();
        $re = array();
        $ce = array();
        $rd = array();
        $in = array();
        $st = array();
        $kw = array();
        $sb = array();
        foreach ($syntax['toplevel'] as $name) {
            $block = $syntax['blocks'][$name];
            if ($block['type'] == 'block') {
                $kwm = array();
                $re[] = '(' . $block['match'] . ')';
                $ce[] = $this->_countSubpatterns($block['match']);
                $rd[] = '';
                $sb[] = false;;
                $st[] = -1;
                foreach ($syntax['keywords'] as $kwname => $kwgroup) {
                    if ($kwgroup['inherits'] != $name) {
                        continue;
                    }
                    $gre = implode('|', array_keys($kwgroup['match']));
                    if (!$kwgroup['case']) {
                        $gre = '(?i)' . $gre;
                    }
                    $kwm[$kwname][] =  $gre;
                    $kwmap[$kwname] = $kwgroup['innerGroup'];
                }
                foreach ($kwm as $g => $ma) {
                    $kwm[$g] = '/^(' . implode(')|(', $ma) . ')$/';
                }
                $kw[] = $kwm;
            } else {
                $kw[] = -1;
                $re[] = '(' . $block['start'] . ')';
                $ce[] = $this->_countSubpatterns($block['start']);
                $rd[] = $block['delimGroup'];
                $st[] = $states[$name];
                $sb[] = $block['remember'];
            }
            $in[] = $block['innerGroup'];
        }
        $re = implode('|', $re);
        $regs[-1] = '/' . $re . '/';
        $counts[-1] = $ce;
        $delim[-1] = $rd;
        $inner[-1] = $in;
        $stat[-1] = $st;
        $keywords[-1] = $kw;
        $subst[-1] = $sb;

        foreach ($syntax['blocks'] as $ablock) {
            if ($ablock['type'] != 'region') {
                continue;
            }
            $end[] = '/' . $ablock['end'] . '/';
            $re = array();
            $ce = array();
            $rd = array();
            $in = array();
            $st = array();
            $kw = array();
            $pc = array();
            $sb = array();
            foreach ((array)@$ablock['lookfor'] as $name) {
                $block = $syntax['blocks'][$name];
                if (isset($block['partClass'])) {
                    $pc[] = $block['partClass'];
                } else {
                    $pc[] = null;
                }
                if ($block['type'] == 'block') {
                    $kwm = array();;
                    $re[] = '(' . $block['match'] . ')';
                    $ce[] = $this->_countSubpatterns($block['match']);
                    $rd[] = '';
                    $sb[] = false;
                    $st[] = -1;
                    foreach ($syntax['keywords'] as $kwname => $kwgroup) {
                        if ($kwgroup['inherits'] != $name) {
                            continue;
                        }
                        $gre = implode('|', array_keys($kwgroup['match']));
                        if (!$kwgroup['case']) {
                            $gre = '(?i)' . $gre;
                        }
                        $kwm[$kwname][] =  $gre;
                        $kwmap[$kwname] = $kwgroup['innerGroup'];
                    }
                    foreach ($kwm as $g => $ma) {
                        $kwm[$g] = '/^(' . implode(')|(', $ma) . ')$/';
                    }
                    $kw[] = $kwm;
                } else {
                    $sb[] = $block['remember'];
                    $kw[] = -1;
                    $re[] = '(' . $block['start'] . ')';
                    $ce[] = $this->_countSubpatterns($block['start']);
                    $rd[] = $block['delimGroup'];
                    $st[] = $states[$name];
                }
                $in[] = $block['innerGroup'];
            }
            $re = implode('|', $re);
            $regs[] = '/' . $re . '/';
            $counts[] = $ce;
            $delim[] = $rd;
            $inner[] = $in;
            $stat[] = $st;
            $keywords[] = $kw;
            $parts[] = $pc;
            $subst[] = $sb;
        }


        $this->_code .= "\n        \$this->_regs = " . $this->_exportArray($regs);
        $this->_code .= ";\n        \$this->_counts = " .$this->_exportArray($counts);
        $this->_code .= ";\n        \$this->_delim = " .$this->_exportArray($delim);
        $this->_code .= ";\n        \$this->_inner = " .$this->_exportArray($inner);
        $this->_code .= ";\n        \$this->_end = " .$this->_exportArray($end);
        $this->_code .= ";\n        \$this->_states = " .$this->_exportArray($stat);
        $this->_code .= ";\n        \$this->_keywords = " .$this->_exportArray($keywords);
        $this->_code .= ";\n        \$this->_parts = " .$this->_exportArray($parts);
        $this->_code .= ";\n        \$this->_subst = " .$this->_exportArray($subst);
        $this->_code .= ";\n        \$this->_conditions = " .$this->_exportArray($conditions);
        $this->_code .= ";\n        \$this->_kwmap = " .$this->_exportArray($kwmap);
        $this->_code .= ";\n        \$this->_defClass = '" .$this->_defClass . '\'';
        $this->_code .= <<<CODE
;
        \$this->_checkDefines();
    }
    
}
CODE;
}

// }}}
}


/*
* Local variables:
* tab-width: 4
* c-basic-offset: 4
* c-hanging-comment-ender-p: nil
* End:
*/

?>
