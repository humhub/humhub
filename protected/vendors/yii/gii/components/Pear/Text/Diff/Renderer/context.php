<?php
/**
 * "Context" diff renderer.
 *
 * This class renders the diff in classic "context diff" format.
 *
 * $Horde: framework/Text_Diff/Diff/Renderer/context.php,v 1.3.2.3 2008/01/04 10:37:27 jan Exp $
 *
 * Copyright 2004-2008 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see http://opensource.org/licenses/lgpl-license.php.
 *
 * @package Text_Diff
 */

/** Text_Diff_Renderer */
require_once 'Text/Diff/Renderer.php';

/**
 * @package Text_Diff
 */
class Text_Diff_Renderer_context extends Text_Diff_Renderer {

    /**
     * Number of leading context "lines" to preserve.
     */
    var $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    var $_trailing_context_lines = 4;

    var $_second_block = '';

    function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        if ($xlen != 1) {
            $xbeg .= ',' . $xlen;
        }
        if ($ylen != 1) {
            $ybeg .= ',' . $ylen;
        }
        $this->_second_block = "--- $ybeg ----\n";
        return "***************\n*** $xbeg ****";
    }

    function _endBlock()
    {
        return $this->_second_block;
    }

    function _context($lines)
    {
        $this->_second_block .= $this->_lines($lines, '  ');
        return $this->_lines($lines, '  ');
    }

    function _added($lines)
    {
        $this->_second_block .= $this->_lines($lines, '+ ');
        return '';
    }

    function _deleted($lines)
    {
        return $this->_lines($lines, '- ');
    }

    function _changed($orig, $final)
    {
        $this->_second_block .= $this->_lines($final, '! ');
        return $this->_lines($orig, '! ');
    }

}
