<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Http.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Json_Server_Request
 */
// require_once 'Zend/Json/Server/Request.php';

/**
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server_Request_Http extends Zend_Json_Server_Request
{
    /**
     * Raw JSON pulled from POST body
     * @var string
     */
    protected $_rawJson;

    /**
     * Constructor
     *
     * Pull JSON request from raw POST body and use to populate request.
     *
     * @return void
     */
    public function __construct()
    {
        $json = file_get_contents('php://input');
        $this->_rawJson = $json;
        if (!empty($json)) {
            $this->loadJson($json);
        }
    }

    /**
     * Get JSON from raw POST body
     *
     * @return string
     */
    public function getRawJson()
    {
        return $this->_rawJson;
    }
}
