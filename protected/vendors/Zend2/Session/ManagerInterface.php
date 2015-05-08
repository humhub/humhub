<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

use Zend\EventManager\EventManagerInterface;
use Zend\Session\Config\ConfigInterface as Config;
use Zend\Session\SaveHandler\SaveHandlerInterface as SaveHandler;
use Zend\Session\Storage\StorageInterface as Storage;

/**
 * Session manager interface
 */
interface ManagerInterface
{
    public function setConfig(Config $config);
    public function getConfig();

    public function setStorage(Storage $storage);
    public function getStorage();

    public function setSaveHandler(SaveHandler $saveHandler);
    public function getSaveHandler();

    public function sessionExists();
    public function start();
    public function destroy();
    public function writeClose();

    public function setName($name);
    public function getName();

    public function setId($id);
    public function getId();
    public function regenerateId();

    public function rememberMe($ttl = null);
    public function forgetMe();
    public function expireSessionCookie();

    public function setValidatorChain(EventManagerInterface $chain);
    public function getValidatorChain();
    public function isValid();
}
