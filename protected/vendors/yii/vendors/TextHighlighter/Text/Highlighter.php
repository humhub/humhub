<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Highlighter base class
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
 * @version    CVS: $Id: Highlighter.php,v 1.1 2007/06/03 02:35:28 ssttoo Exp $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

// {{{ BC constants

// BC trick : define constants related to default
// renderer if needed
if (!defined('HL_NUMBERS_LI')) {
    /**#@+
     * Constant for use with $options['numbers']
     * @see Text_Highlighter_Renderer_Html::_init()
     */
    /**
     * use numbered list
     */
    define ('HL_NUMBERS_LI'    ,    1);
    /**
     * Use 2-column table with line numbers in left column and code in  right column.
     * Forces $options['tag'] = HL_TAG_PRE
     */
    define ('HL_NUMBERS_TABLE'    , 2);
    /**#@-*/
}

// }}}
// {{{ constants
/**
 * for our purpose, it is infinity
 */
define ('HL_INFINITY',      1000000000);

// }}}

/**
 * Text highlighter base class
 *
 * @author     Andrey Demenev <demenev@gmail.com>
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.1
 * @link       http://pear.php.net/package/Text_Highlighter
 */

// {{{ Text_Highlighter

/**
 * Text highlighter base class
 *
 * This class implements all functions necessary for highlighting,
 * but it does not contain highlighting rules. Actual highlighting is
 * done using a descendent of this class.
 *
 * One is not supposed to manually create descendent classes.
 * Instead, describe highlighting  rules in XML format and
 * use {@link Text_Highlighter_Generator} to create descendent class.
 * Alternatively, an instance of a descendent class can be created
 * directly.
 *
 * Use {@link Text_Highlighter::factory()} to create an
 * object for particular language highlighter
 *
 * Usage example
 * <code>
 *require_once 'Text/Highlighter.php';
 *$hlSQL =& Text_Highlighter::factory('SQL',array('numbers'=>true));
 *echo $hlSQL->highlight('SELECT * FROM table a WHERE id = 12');
 * </code>
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @package Text_Highlighter
 * @access public
 */

class Text_Highlighter
{
    // {{{ members
    
    /**
     * Syntax highlighting rules.
     * Auto-generated classes set this var
     *
     * @access protected
     * @see _init
     * @var array
     */
    var $_syntax;

    /**
     * Renderer object.
     *
     * @access private
     * @var array
     */
    var $_renderer;

    /**
     * Options. Keeped for BC
     *
     * @access protected
     * @var array
     */
    var $_options = array();

    /**
     * Conditionds
     *
     * @access protected
     * @var array
     */
    var $_conditions = array();

    /**
     * Disabled keywords
     *
     * @access protected
     * @var array
     */
    var $_disabled = array();

    /**
     * Language
     *
     * @access protected
     * @var string
     */
    var $_language = '';

    // }}}
    // {{{ _checkDefines
    
    /**
     * Called by subclssses' constructors to enable/disable
     * optional highlighter rules
     *
     * @param array $defines  Conditional defines
     *
     * @access protected
     */
    function _checkDefines()
    {
        if (isset($this->_options['defines'])) {
            $defines = $this->_options['defines'];
        } else {
            $defines = array();
        }
        foreach ($this->_conditions as $name => $actions) {
            foreach($actions as $action) {
                $present = in_array($name, $defines);
                if (!$action[1]) {
                    $present = !$present;
                }
                if ($present) {
                    unset($this->_disabled[$action[0]]);
                } else {
                    $this->_disabled[$action[0]] = true;
                }
            }
        }
    }

    // }}}
    // {{{ factory
    
    /**
     * Create a new Highlighter object for specified language
     *
     * @param string $lang    language, for example "SQL"
     * @param array  $options Rendering options. This
     * parameter is only keeped for BC reasons, use 
     * {@link Text_Highlighter::setRenderer()} instead
     *
     * @return mixed a newly created Highlighter object, or 
     * a PEAR error object on error
     *
     * @static
     * @access public
     */
    public static function factory($lang, $options = array())
    {
        $lang = strtoupper($lang);
        $langFile = dirname(__FILE__)."/Highlighter/$lang.php";
        if (is_file($langFile))
        	include_once $langFile;
        else
        	return false;

        $classname = 'Text_Highlighter_' . $lang;

        if (!class_exists($classname))
        	return false;

		return new $classname($options);
    }

    // }}}
    // {{{ setRenderer
    
    /**
     * Set renderer object
     *
     * @param object $renderer  Text_Highlighter_Renderer
     *
     * @access public
     */
    function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
    }

    // }}}

    /**
     * Helper function to find matching brackets
     *
     * @access private
     */
    function _matchingBrackets($str)
    {
        return strtr($str, '()<>[]{}', ')(><][}{');
    }


    
    
    function _getToken()
    {
        if (!empty($this->_tokenStack)) {
            return array_pop($this->_tokenStack);
        }
        if ($this->_pos >= $this->_len) {
            return NULL;
        }

        if ($this->_state != -1 && preg_match($this->_endpattern, $this->_str, $m, PREG_OFFSET_CAPTURE, $this->_pos)) {
            $endpos = $m[0][1];
            $endmatch = $m[0][0];
        } else {
            $endpos = -1;
        }
        preg_match ($this->_regs[$this->_state], $this->_str, $m, PREG_OFFSET_CAPTURE, $this->_pos);
        $n = 1;
 
 
         foreach ($this->_counts[$this->_state] as $i=>$count) {
            if (!isset($m[$n])) {
                break;
            }
            if ($m[$n][1]>-1 && ($endpos == -1 || $m[$n][1] < $endpos)) {
                if ($this->_states[$this->_state][$i] != -1) {
                    $this->_tokenStack[] = array($this->_delim[$this->_state][$i], $m[$n][0]);
                } else {
                    $inner = $this->_inner[$this->_state][$i];
                    if (isset($this->_parts[$this->_state][$i])) {
                        $parts = array();
                        $partpos = $m[$n][1];
                        for ($j=1; $j<=$count; $j++) {
                            if ($m[$j+$n][1] < 0) {
                                continue;
                            }
                            if (isset($this->_parts[$this->_state][$i][$j])) {
                                if ($m[$j+$n][1] > $partpos) {
                                    array_unshift($parts, array($inner, substr($this->_str, $partpos, $m[$j+$n][1]-$partpos)));
                                }
                                array_unshift($parts, array($this->_parts[$this->_state][$i][$j], $m[$j+$n][0]));
                            }
                            $partpos = $m[$j+$n][1] + strlen($m[$j+$n][0]);
                        }
                        if ($partpos < $m[$n][1] + strlen($m[$n][0])) {
                            array_unshift($parts, array($inner, substr($this->_str, $partpos, $m[$n][1] - $partpos + strlen($m[$n][0]))));
                        }
                        $this->_tokenStack = array_merge($this->_tokenStack, $parts);
                    } else {
                        foreach ($this->_keywords[$this->_state][$i] as $g => $re) {
                            if (isset($this->_disabled[$g])) {
                                continue;
                            }
                            if (preg_match($re, $m[$n][0])) {
                                $inner = $this->_kwmap[$g];
                                break;
                            }
                        }
                        $this->_tokenStack[] = array($inner, $m[$n][0]);
                    }
                }
                if ($m[$n][1] > $this->_pos) {
                    $this->_tokenStack[] = array($this->_lastinner, substr($this->_str, $this->_pos, $m[$n][1]-$this->_pos));
                }
                $this->_pos = $m[$n][1] + strlen($m[$n][0]);
                if ($this->_states[$this->_state][$i] != -1) {
                    $this->_stack[] = array($this->_state, $this->_lastdelim, $this->_lastinner, $this->_endpattern);
                    $this->_lastinner = $this->_inner[$this->_state][$i];
                    $this->_lastdelim = $this->_delim[$this->_state][$i];
                    $l = $this->_state;
                    $this->_state = $this->_states[$this->_state][$i];
                    $this->_endpattern = $this->_end[$this->_state];
                    if ($this->_subst[$l][$i]) {
                        for ($k=0; $k<=$this->_counts[$l][$i]; $k++) {
                            if (!isset($m[$i+$k])) {
                                break;
                            }
                            $quoted = preg_quote($m[$n+$k][0], '/');
                            $this->_endpattern = str_replace('%'.$k.'%', $quoted, $this->_endpattern);
                            $this->_endpattern = str_replace('%b'.$k.'%', $this->_matchingBrackets($quoted), $this->_endpattern);
                        }
                    }
                }
                return array_pop($this->_tokenStack);
            }
            $n += $count + 1;
        }

        if ($endpos > -1) {
            $this->_tokenStack[] = array($this->_lastdelim, $endmatch);
            if ($endpos > $this->_pos) {
                $this->_tokenStack[] = array($this->_lastinner, substr($this->_str, $this->_pos, $endpos-$this->_pos));
            }
            list($this->_state, $this->_lastdelim, $this->_lastinner, $this->_endpattern) = array_pop($this->_stack);
            $this->_pos = $endpos + strlen($endmatch);
            return array_pop($this->_tokenStack);
        }
        $p = $this->_pos;
        $this->_pos = HL_INFINITY;
        return array($this->_lastinner, substr($this->_str, $p));
    }
    
    
    
    
    // {{{ highlight

    /**
     * Highlights code
     *
     * @param  string $str      Code to highlight
     * @access public
     * @return string Highlighted text
     *
     */

    function highlight($str)
    {
        if (!($this->_renderer)) {
            include_once(dirname(__FILE__).'/Renderer/Html.php');
            $this->_renderer = new Text_Highlighter_Renderer_Html($this->_options);
        }
        $this->_state = -1;
        $this->_pos = 0;
        $this->_stack = array();
        $this->_tokenStack = array();
        $this->_lastinner = $this->_defClass;
        $this->_lastdelim = $this->_defClass;
        $this->_endpattern = '';
        $this->_renderer->reset();
        $this->_renderer->setCurrentLanguage($this->_language);
        $this->_str = $this->_renderer->preprocess($str);
        $this->_len = strlen($this->_str);
        while ($token = $this->_getToken()) {
            $this->_renderer->acceptToken($token[0], $token[1]);
        }
        $this->_renderer->finalize();
        return $this->_renderer->getOutput();
    }
    
    // }}}
    
}

// }}}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
