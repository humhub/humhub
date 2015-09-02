<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * XML renderer.
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
 * @version    CVS: $Id: XML.php,v 1.1 2007/06/03 02:37:09 ssttoo Exp $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

/**
 * @ignore
 */

require_once dirname(__FILE__).'/../Renderer.php';
require_once dirname(__FILE__).'/../Renderer/Array.php';

/**
 * XML renderer, based on Andrey Demenev's HTML renderer.
 *
 * @author     Stoyan Stefanov <ssttoo@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2006 Stoyan Stefanov
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.5.0
 * @link       http://pear.php.net/package/Text_Highlighter
 */

class Text_Highlighter_Renderer_XML extends Text_Highlighter_Renderer_Array
{


    /**
     * Options for XML_Serializer
     *
     * @access private
     * @var array
     */
    var $_serializer_options = array();


    /**
     * Resets renderer state
     *
     * Descendents of Text_Highlighter call this method from the constructor,
     * passing $options they get as parameter.
     *
     * @access protected
     */
    function reset()
    {
        parent::reset();
        if (isset($this->_options['xml_serializer'])) {
            $this->_serializer_options = $this->_options['xml_serializer'];
        }
    }


    /**
     * Signals that no more tokens are available
     *
     * @abstract
     * @access public
     */
    function finalize()
    {

        // call parent's finalize(), then serialize array into XML
        parent::finalize();
        $output = parent::getOutput();

        $serializer = new XML_Serializer($this->_serializer_options);
        $result = $serializer->serialize($output);
        if ($result === true) {
            $this->_output = $serializer->getSerializedData();
        }
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