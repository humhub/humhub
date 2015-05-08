<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage;

use Zend\Stdlib\ErrorHandler;

class Mbox extends AbstractStorage
{
    /**
     * file handle to mbox file
     * @var null|resource
     */
    protected $fh;

    /**
     * filename of mbox file for __wakeup
     * @var string
     */
    protected $filename;

    /**
     * modification date of mbox file for __wakeup
     * @var int
     */
    protected $filemtime;

    /**
     * start and end position of messages as array('start' => start, 'separator' => headersep, 'end' => end)
     * @var array
     */
    protected $positions;

    /**
     * used message class, change it in an extended class to extend the returned message class
     * @var string
     */
    protected $messageClass = '\Zend\Mail\Storage\Message\File';

    /**
     * Count messages all messages in current box
     *
     * @return int number of messages
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function countMessages()
    {
        return count($this->positions);
    }


    /**
     * Get a list of messages with number and size
     *
     * @param  int|null $id  number of message or null for all messages
     * @return int|array size of given message of list with all messages as array(num => size)
     */
    public function getSize($id = 0)
    {
        if ($id) {
            $pos = $this->positions[$id - 1];
            return $pos['end'] - $pos['start'];
        }

        $result = array();
        foreach ($this->positions as $num => $pos) {
            $result[$num + 1] = $pos['end'] - $pos['start'];
        }

        return $result;
    }


    /**
     * Get positions for mail message or throw exception if id is invalid
     *
     * @param int $id number of message
     * @throws Exception\InvalidArgumentException
     * @return array positions as in positions
     */
    protected function getPos($id)
    {
        if (!isset($this->positions[$id - 1])) {
            throw new Exception\InvalidArgumentException('id does not exist');
        }

        return $this->positions[$id - 1];
    }


    /**
     * Fetch a message
     *
     * @param  int $id number of message
     * @return \Zend\Mail\Storage\Message\File
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getMessage($id)
    {
        // TODO that's ugly, would be better to let the message class decide
        if (strtolower($this->messageClass) == '\zend\mail\storage\message\file'
            || is_subclass_of($this->messageClass, '\Zend\Mail\Storage\Message\File')) {
            // TODO top/body lines
            $messagePos = $this->getPos($id);
            return new $this->messageClass(array('file' => $this->fh, 'startPos' => $messagePos['start'],
                                                  'endPos' => $messagePos['end']));
        }

        $bodyLines = 0; // TODO: need a way to change that

        $message = $this->getRawHeader($id);
        // file pointer is after headers now
        if ($bodyLines) {
            $message .= "\n";
            while ($bodyLines-- && ftell($this->fh) < $this->positions[$id - 1]['end']) {
                $message .= fgets($this->fh);
            }
        }

        return new $this->messageClass(array('handler' => $this, 'id' => $id, 'headers' => $message));
    }

    /*
     * Get raw header of message or part
     *
     * @param  int               $id       number of message
     * @param  null|array|string $part     path to part or null for message header
     * @param  int               $topLines include this many lines with header (after an empty line)
     * @return string raw header
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getRawHeader($id, $part = null, $topLines = 0)
    {
        if ($part !== null) {
            // TODO: implement
            throw new Exception\RuntimeException('not implemented');
        }
        $messagePos = $this->getPos($id);
        // TODO: toplines
        return stream_get_contents($this->fh, $messagePos['separator'] - $messagePos['start'], $messagePos['start']);
    }

    /*
     * Get raw content of message or part
     *
     * @param  int               $id   number of message
     * @param  null|array|string $part path to part or null for message content
     * @return string raw content
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getRawContent($id, $part = null)
    {
        if ($part !== null) {
            // TODO: implement
            throw new Exception\RuntimeException('not implemented');
        }
        $messagePos = $this->getPos($id);
        return stream_get_contents($this->fh, $messagePos['end'] - $messagePos['separator'], $messagePos['separator']);
    }

    /**
     * Create instance with parameters
     * Supported parameters are:
     *   - filename filename of mbox file
     *
     * @param  $params array mail reader specific parameters
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object) $params;
        }

        if (!isset($params->filename)) {
            throw new Exception\InvalidArgumentException('no valid filename given in params');
        }

        $this->openMboxFile($params->filename);
        $this->has['top']      = true;
        $this->has['uniqueid'] = false;
    }

    /**
     * check if given file is a mbox file
     *
     * if $file is a resource its file pointer is moved after the first line
     *
     * @param  resource|string $file stream resource of name of file
     * @param  bool $fileIsString file is string or resource
     * @return bool file is mbox file
     */
    protected function isMboxFile($file, $fileIsString = true)
    {
        if ($fileIsString) {
            ErrorHandler::start(E_WARNING);
            $file = fopen($file, 'r');
            ErrorHandler::stop();
            if (!$file) {
                return false;
            }
        } else {
            fseek($file, 0);
        }

        $result = false;

        $line = fgets($file);
        if (strpos($line, 'From ') === 0) {
            $result = true;
        }

        if ($fileIsString) {
            ErrorHandler::start(E_WARNING);
            fclose($file);
            ErrorHandler::stop();
        }

        return $result;
    }

    /**
     * open given file as current mbox file
     *
     * @param  string $filename filename of mbox file
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    protected function openMboxFile($filename)
    {
        if ($this->fh) {
            $this->close();
        }

        ErrorHandler::start();
        $this->fh = fopen($filename, 'r');
        $error = ErrorHandler::stop();
        if (!$this->fh) {
            throw new Exception\RuntimeException('cannot open mbox file', 0, $error);
        }
        $this->filename = $filename;
        $this->filemtime = filemtime($this->filename);

        if (!$this->isMboxFile($this->fh, false)) {
            ErrorHandler::start(E_WARNING);
            fclose($this->fh);
            $error = ErrorHandler::stop();
            throw new Exception\InvalidArgumentException('file is not a valid mbox format', 0, $error);
        }

        $messagePos = array('start' => ftell($this->fh), 'separator' => 0, 'end' => 0);
        while (($line = fgets($this->fh)) !== false) {
            if (strpos($line, 'From ') === 0) {
                $messagePos['end'] = ftell($this->fh) - strlen($line) - 2; // + newline
                if (!$messagePos['separator']) {
                    $messagePos['separator'] = $messagePos['end'];
                }
                $this->positions[] = $messagePos;
                $messagePos = array('start' => ftell($this->fh), 'separator' => 0, 'end' => 0);
            }
            if (!$messagePos['separator'] && !trim($line)) {
                $messagePos['separator'] = ftell($this->fh);
            }
        }

        $messagePos['end'] = ftell($this->fh);
        if (!$messagePos['separator']) {
            $messagePos['separator'] = $messagePos['end'];
        }
        $this->positions[] = $messagePos;
    }

    /**
     * Close resource for mail lib. If you need to control, when the resource
     * is closed. Otherwise the destructor would call this.
     *
     */
    public function close()
    {
        ErrorHandler::start(E_WARNING);
        fclose($this->fh);
        ErrorHandler::stop();
        $this->positions = array();
    }


    /**
     * Waste some CPU cycles doing nothing.
     *
     * @return bool always return true
     */
    public function noop()
    {
        return true;
    }


    /**
     * stub for not supported message deletion
     *
     * @param $id
     * @throws Exception\RuntimeException
     */
    public function removeMessage($id)
    {
        throw new Exception\RuntimeException('mbox is read-only');
    }

    /**
     * get unique id for one or all messages
     *
     * Mbox does not support unique ids (yet) - it's always the same as the message number.
     * That shouldn't be a problem, because we can't change mbox files. Therefor the message
     * number is save enough.
     *
     * @param int|null $id message number
     * @return array|string message number for given message or all messages as array
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getUniqueId($id = null)
    {
        if ($id) {
            // check if id exists
            $this->getPos($id);
            return $id;
        }

        $range = range(1, $this->countMessages());
        return array_combine($range, $range);
    }

    /**
     * get a message number from a unique id
     *
     * I.e. if you have a webmailer that supports deleting messages you should use unique ids
     * as parameter and use this method to translate it to message number right before calling removeMessage()
     *
     * @param string $id unique id
     * @return int message number
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getNumberByUniqueId($id)
    {
        // check if id exists
        $this->getPos($id);
        return $id;
    }

    /**
     * magic method for serialize()
     *
     * with this method you can cache the mbox class
     *
     * @return array name of variables
     */
    public function __sleep()
    {
        return array('filename', 'positions', 'filemtime');
    }

    /**
     * magic method for unserialize()
     *
     * with this method you can cache the mbox class
     * for cache validation the mtime of the mbox file is used
     *
     * @throws Exception\RuntimeException
     */
    public function __wakeup()
    {
        ErrorHandler::start();
        $filemtime = filemtime($this->filename);
        ErrorHandler::stop();
        if ($this->filemtime != $filemtime) {
            $this->close();
            $this->openMboxFile($this->filename);
        } else {
            ErrorHandler::start();
            $this->fh = fopen($this->filename, 'r');
            $error    = ErrorHandler::stop();
            if (!$this->fh) {
                throw new Exception\RuntimeException('cannot open mbox file', 0, $error);
            }
        }
    }
}
