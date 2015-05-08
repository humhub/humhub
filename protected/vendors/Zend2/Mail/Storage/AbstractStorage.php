<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage;

use ArrayAccess;
use Countable;
use SeekableIterator;

abstract class AbstractStorage implements
    ArrayAccess,
    Countable,
    SeekableIterator
{
    /**
     * class capabilities with default values
     * @var array
     */
    protected $has = array('uniqueid'  => true,
                            'delete'    => false,
                            'create'    => false,
                            'top'       => false,
                            'fetchPart' => true,
                            'flags'     => false);

    /**
     * current iteration position
     * @var int
     */
    protected $iterationPos = 0;

    /**
     * maximum iteration position (= message count)
     * @var null|int
     */
    protected $iterationMax = null;

    /**
     * used message class, change it in an extended class to extend the returned message class
     * @var string
     */
    protected $messageClass = 'Zend\Mail\Storage\Message';

    /**
     * Getter for has-properties. The standard has properties
     * are: hasFolder, hasUniqueid, hasDelete, hasCreate, hasTop
     *
     * The valid values for the has-properties are:
     *   - true if a feature is supported
     *   - false if a feature is not supported
     *   - null is it's not yet known or it can't be know if a feature is supported
     *
     * @param  string $var  property name
     * @throws Exception\InvalidArgumentException
     * @return bool         supported or not
     */
    public function __get($var)
    {
        if (strpos($var, 'has') === 0) {
            $var = strtolower(substr($var, 3));
            return isset($this->has[$var]) ? $this->has[$var] : null;
        }

        throw new Exception\InvalidArgumentException($var . ' not found');
    }


    /**
     * Get a full list of features supported by the specific mail lib and the server
     *
     * @return array list of features as array(feature_name => true|false[|null])
     */
    public function getCapabilities()
    {
        return $this->has;
    }


    /**
     * Count messages messages in current box/folder
     *
     * @return int number of messages
     * @throws Exception\ExceptionInterface
     */
    abstract public function countMessages();


    /**
     * Get a list of messages with number and size
     *
     * @param  int $id  number of message
     * @return int|array size of given message of list with all messages as array(num => size)
     */
    abstract public function getSize($id = 0);


    /**
     * Get a message with headers and body
     *
     * @param  $id int number of message
     * @return Message
     */
    abstract public function getMessage($id);


    /**
     * Get raw header of message or part
     *
     * @param  int               $id       number of message
     * @param  null|array|string $part     path to part or null for message header
     * @param  int               $topLines include this many lines with header (after an empty line)
     * @return string raw header
     */
    abstract public function getRawHeader($id, $part = null, $topLines = 0);

    /**
     * Get raw content of message or part
     *
     * @param  int               $id   number of message
     * @param  null|array|string $part path to part or null for message content
     * @return string raw content
     */
    abstract public function getRawContent($id, $part = null);

    /**
     * Create instance with parameters
     *
     * @param  array $params mail reader specific parameters
     * @throws Exception\ExceptionInterface
     */
    abstract public function __construct($params);


    /**
     * Destructor calls close() and therefore closes the resource.
     */
    public function __destruct()
    {
        $this->close();
    }


    /**
     * Close resource for mail lib. If you need to control, when the resource
     * is closed. Otherwise the destructor would call this.
     */
    abstract public function close();


    /**
     * Keep the resource alive.
     */
    abstract public function noop();

    /**
     * delete a message from current box/folder
     *
     * @param $id
     */
    abstract public function removeMessage($id);

    /**
     * get unique id for one or all messages
     *
     * if storage does not support unique ids it's the same as the message number
     *
     * @param int|null $id message number
     * @return array|string message number for given message or all messages as array
     * @throws Exception\ExceptionInterface
     */
    abstract public function getUniqueId($id = null);

    /**
     * get a message number from a unique id
     *
     * I.e. if you have a webmailer that supports deleting messages you should use unique ids
     * as parameter and use this method to translate it to message number right before calling removeMessage()
     *
     * @param string $id unique id
     * @return int message number
     * @throws Exception\ExceptionInterface
     */
    abstract public function getNumberByUniqueId($id);

    // interface implementations follows

    /**
     * Countable::count()
     *
     * @return   int
     */
     public function count()
     {
        return $this->countMessages();
     }


     /**
      * ArrayAccess::offsetExists()
      *
      * @param    int     $id
      * @return   bool
      */
     public function offsetExists($id)
     {
        try {
            if ($this->getMessage($id)) {
                return true;
            }
        } catch (Exception\ExceptionInterface $e) {}

        return false;
     }


     /**
      * ArrayAccess::offsetGet()
      *
      * @param    int $id
      * @return   \Zend\Mail\Storage\Message message object
      */
     public function offsetGet($id)
     {
        return $this->getMessage($id);
     }


    /**
     * ArrayAccess::offsetSet()
     *
     * @param mixed $id
     * @param mixed $value
     * @throws Exception\RuntimeException
     */
     public function offsetSet($id, $value)
     {
        throw new Exception\RuntimeException('cannot write mail messages via array access');
     }


     /**
      * ArrayAccess::offsetUnset()
      *
      * @param    int   $id
      * @return   bool success
      */
     public function offsetUnset($id)
     {
        return $this->removeMessage($id);
     }


     /**
      * Iterator::rewind()
      *
      * Rewind always gets the new count from the storage. Thus if you use
      * the interfaces and your scripts take long you should use reset()
      * from time to time.
      */
     public function rewind()
     {
        $this->iterationMax = $this->countMessages();
        $this->iterationPos = 1;
     }


     /**
      * Iterator::current()
      *
      * @return   Message current message
      */
     public function current()
     {
        return $this->getMessage($this->iterationPos);
     }


     /**
      * Iterator::key()
      *
      * @return   int id of current position
      */
     public function key()
     {
        return $this->iterationPos;
     }


     /**
      * Iterator::next()
      */
     public function next()
     {
        ++$this->iterationPos;
     }


     /**
      * Iterator::valid()
      *
      * @return bool
      */
     public function valid()
     {
        if ($this->iterationMax === null) {
          $this->iterationMax = $this->countMessages();
        }
        return $this->iterationPos && $this->iterationPos <= $this->iterationMax;
     }


     /**
      * SeekableIterator::seek()
      *
      * @param  int $pos
      * @throws Exception\OutOfBoundsException
      */
     public function seek($pos)
     {
        if ($this->iterationMax === null) {
          $this->iterationMax = $this->countMessages();
        }

        if ($pos > $this->iterationMax) {
            throw new Exception\OutOfBoundsException('this position does not exist');
        }
        $this->iterationPos = $pos;
     }
}
