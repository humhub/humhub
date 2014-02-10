<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * JSON renderer.
 *
 * Based on the HTML renderer by Andrey Demenev.
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package    Text_Highlighter
 * @author     Stoyan Stefanov <ssttoo@gmail.com>
 * @copyright  2006 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    CVS: $Id: JSON.php,v 1.1 2007/06/03 02:37:09 ssttoo Exp $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

/**
 * @ignore
 */

require_once dirname(__FILE__).'/../Renderer.php';
require_once dirname(__FILE__).'/../Renderer/Array.php';

/**
 * JSON renderer, based on Andrey Demenev's HTML renderer.
 *
 * @author     Stoyan Stefanov <ssttoo@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2006 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.5.0
 * @link       http://pear.php.net/package/Text_Highlighter
 */

class Text_Highlighter_Renderer_JSON extends Text_Highlighter_Renderer_Array
{

    /**
     * Signals that no more tokens are available
     *
     * @abstract
     * @access public
     */
    function finalize()
    {

        parent::finalize();
        $output = parent::getOutput();

        $json_array = array();

        foreach ($output AS $token) {

            if ($this->_enumerated) {
                $json_array[] = '["' . $token[0] . '","' . $token[1] . '"]';
            } else {
                $key = key($token);
                $json_array[] = '{"class": "' . $key . '","content":"' . $token[$key] . '"}';
            }

        }

        $this->_output  = '['. implode(',', $json_array) .']';
        $this->_output = str_replace("\n", '\n', $this->_output);

    }


}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>