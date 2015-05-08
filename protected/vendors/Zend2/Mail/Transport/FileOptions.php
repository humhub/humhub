<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Transport;

use Zend\Mail\Exception;
use Zend\Stdlib\AbstractOptions;

class FileOptions extends AbstractOptions
{
    /**
     * @var string Local client hostname
     */
    protected $path;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * Set path to stored mail files
     *
     * @param  string $path
     * @throws \Zend\Mail\Exception\InvalidArgumentException
     * @return FileOptions
     */
    public function setPath($path)
    {
        if (!is_dir($path) || !is_writable($path)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a valid path in which to write mail files; received "%s"',
                __METHOD__,
                (string) $path
            ));
        }
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * If none is set, uses value from sys_get_temp_dir()
     *
     * @return string
     */
    public function getPath()
    {
        if (null === $this->path) {
            $this->setPath(sys_get_temp_dir());
        }
        return $this->path;
    }

    /**
     * Set callback used to generate a file name
     *
     * @param  callable $callback
     * @throws \Zend\Mail\Exception\InvalidArgumentException
     * @return FileOptions
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a valid callback; received "%s"',
                __METHOD__,
                (is_object($callback) ? get_class($callback) : gettype($callback))
            ));
        }
        $this->callback = $callback;
        return $this;
    }

    /**
     * Get callback used to generate a file name
     *
     * @return callable
     */
    public function getCallback()
    {
        if (null === $this->callback) {
            $this->setCallback(function ($transport) {
                return 'ZendMail_' . time() . '_' . mt_rand() . '.tmp';
            });
        }
        return $this->callback;
    }
}
