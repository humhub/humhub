<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Protocol;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for SMTP extensions.
 *
 * Enforces that SMTP extensions retrieved are instances of Smtp. Additionally,
 * it registers a number of default extensions available.
 */
class SmtpPluginManager extends AbstractPluginManager
{
    /**
     * Default set of extensions
     *
     * @var array
     */
    protected $invokableClasses = array(
        'crammd5' => 'Zend\Mail\Protocol\Smtp\Auth\Crammd5',
        'login'   => 'Zend\Mail\Protocol\Smtp\Auth\Login',
        'plain'   => 'Zend\Mail\Protocol\Smtp\Auth\Plain',
        'smtp'    => 'Zend\Mail\Protocol\Smtp',
    );

    /**
     * Validate the plugin
     *
     * Checks that the extension loaded is an instance of Smtp.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Smtp) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must extend %s\Smtp',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
