<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Server;

/**
 * XMLRPC Server Faults
 *
 * Encapsulates an exception for use as an XMLRPC fault response. Valid
 * exception classes that may be used for generating the fault code and fault
 * string can be attached using {@link attachFaultException()}; all others use a
 * generic '404 Unknown error' response.
 *
 * You may also attach fault observers, which would allow you to monitor
 * particular fault cases; this is done via {@link attachObserver()}. Observers
 * need only implement a static 'observe' method.
 *
 * To allow method chaining, you may use the {@link getInstance()} factory
 * to instantiate a Zend\XmlRpc\Server\Fault.
 */
class Fault extends \Zend\XmlRpc\Fault
{
    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * @var array Array of exception classes that may define xmlrpc faults
     */
    protected static $faultExceptionClasses = array('Zend\\XmlRpc\\Server\\Exception\\ExceptionInterface' => true);

    /**
     * @var array Array of fault observers
     */
    protected static $observers = array();

    /**
     * Constructor
     *
     * @param  \Exception $e
     * @return Fault
     */
    public function __construct(\Exception $e)
    {
        $this->exception = $e;
        $code             = 404;
        $message          = 'Unknown error';

        foreach (array_keys(static::$faultExceptionClasses) as $class) {
            if ($e instanceof $class) {
                $code    = $e->getCode();
                $message = $e->getMessage();
                break;
            }
        }

        parent::__construct($code, $message);

        // Notify exception observers, if present
        if (!empty(static::$observers)) {
            foreach (array_keys(static::$observers) as $observer) {
                $observer::observe($this);
            }
        }
    }

    /**
     * Return Zend\XmlRpc\Server\Fault instance
     *
     * @param \Exception $e
     * @return Fault
     */
    public static function getInstance(\Exception $e)
    {
        return new static($e);
    }

    /**
     * Attach valid exceptions that can be used to define xmlrpc faults
     *
     * @param string|array $classes Class name or array of class names
     * @return void
     */
    public static function attachFaultException($classes)
    {
        if (!is_array($classes)) {
            $classes = (array) $classes;
        }

        foreach ($classes as $class) {
            if (is_string($class) && class_exists($class)) {
                static::$faultExceptionClasses[$class] = true;
            }
        }
    }

    /**
     * Detach fault exception classes
     *
     * @param string|array $classes Class name or array of class names
     * @return void
     */
    public static function detachFaultException($classes)
    {
        if (!is_array($classes)) {
            $classes = (array) $classes;
        }

        foreach ($classes as $class) {
            if (is_string($class) && isset(static::$faultExceptionClasses[$class])) {
                unset(static::$faultExceptionClasses[$class]);
            }
        }
    }

    /**
     * Attach an observer class
     *
     * Allows observation of xmlrpc server faults, thus allowing logging or mail
     * notification of fault responses on the xmlrpc server.
     *
     * Expects a valid class name; that class must have a public static method
     * 'observe' that accepts an exception as its sole argument.
     *
     * @param string $class
     * @return bool
     */
    public static function attachObserver($class)
    {
        if (!is_string($class)
            || !class_exists($class)
            || !is_callable(array($class, 'observe')))
        {
            return false;
        }

        if (!isset(static::$observers[$class])) {
            static::$observers[$class] = true;
        }

        return true;
    }

    /**
     * Detach an observer
     *
     * @param string $class
     * @return bool
     */
    public static function detachObserver($class)
    {
        if (!isset(static::$observers[$class])) {
            return false;
        }

        unset(static::$observers[$class]);
        return true;
    }

    /**
     * Retrieve the exception
     *
     * @access public
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
