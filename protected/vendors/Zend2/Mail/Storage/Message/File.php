<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage\Message;

use Zend\Mail\Storage\Part;

class File extends Part\File implements MessageInterface
{
    /**
     * flags for this message
     * @var array
     */
    protected $flags = array();

    /**
     * Public constructor
     *
     * In addition to the parameters of Zend\Mail\Storage\Part::__construct() this constructor supports:
     * - flags array with flags for message, keys are ignored, use constants defined in Zend\Mail\Storage
     *
     * @param  array $params
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function __construct(array $params)
    {
        if (!empty($params['flags'])) {
            // set key and value to the same value for easy lookup
            $this->flags = array_combine($params['flags'], $params['flags']);
        }

        parent::__construct($params);
    }

    /**
     * return toplines as found after headers
     *
     * @return string toplines
     */
    public function getTopLines()
    {
        return $this->topLines;
    }

    /**
     * check if flag is set
     *
     * @param mixed $flag a flag name, use constants defined in \Zend\Mail\Storage
     * @return bool true if set, otherwise false
     */
    public function hasFlag($flag)
    {
        return isset($this->flags[$flag]);
    }

    /**
     * get all set flags
     *
     * @return array array with flags, key and value are the same for easy lookup
     */
    public function getFlags()
    {
        return $this->flags;
    }
}
