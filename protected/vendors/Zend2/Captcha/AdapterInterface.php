<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Captcha;

use Zend\Validator\ValidatorInterface;

/**
 * Generic Captcha adapter interface
 *
 * Each specific captcha implementation should implement this interface
 */
interface AdapterInterface extends ValidatorInterface
{
    /**
     * Generate a new captcha
     *
     * @return string new captcha ID
     */
    public function generate();

    /**
     * Set captcha name
     *
     * @param  string $name
     * @return AdapterInterface
     */
    public function setName($name);

    /**
     * Get captcha name
     *
     * @return string
     */
    public function getName();

    /**
     * Get helper name to use when rendering this captcha type
     *
     * @return string
     */
    public function getHelperName();
}
