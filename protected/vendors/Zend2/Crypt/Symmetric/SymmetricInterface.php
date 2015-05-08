<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Symmetric;

interface SymmetricInterface
{
    public function encrypt($data);

    public function decrypt($data);

    public function setKey($key);

    public function getKey();

    public function getKeySize();

    public function getAlgorithm();

    public function setAlgorithm($algo);

    public function getSupportedAlgorithms();

    public function setSalt($salt);

    public function getSalt();

    public function getSaltSize();

    public function getBlockSize();

    public function setMode($mode);

    public function getMode();

    public function getSupportedModes();
}
