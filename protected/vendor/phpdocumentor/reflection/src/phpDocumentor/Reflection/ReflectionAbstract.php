<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2012 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

use phpDocumentor\Event\DebugEvent;
use phpDocumentor\Event\Dispatcher;
use phpDocumentor\Event\LogEvent;

/**
 * Provides basic event logging and dispatching for every reflection class.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
abstract class ReflectionAbstract
{
    /**
     * The context (namespace, aliases) for the reflection.
     *
     * @var \phpDocumentor\Reflection\DocBlock\Context
     */
    protected $context = null;

    /**
     * Dispatches a logging request.
     *
     * @param string $message  The message to log.
     * @param int    $priority The logging priority, the lower,
     *  the more important. Ranges from 1 to 7
     *
     * @return void
     */
    public function log($message, $priority = 6)
    {
        if (class_exists('phpDocumentor\Event\Dispatcher')) {
            Dispatcher::getInstance()->dispatch(
                'system.log',
                LogEvent::createInstance($this)
                ->setMessage($message)->setPriority($priority)
            );
        }
    }

    /**
     * Dispatches a logging request to log a debug message.
     *
     * @param string $message The message to log.
     *
     * @return void
     */
    public function debug($message)
    {
        if (class_exists('phpDocumentor\Event\Dispatcher')) {
            Dispatcher::getInstance()->dispatch(
                'system.debug',
                DebugEvent::createInstance($this)
                ->setMessage($message)
            );
        }
    }
}
