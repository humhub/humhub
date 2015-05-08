<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage;

use Zend\Mail;
use Zend\Stdlib\ErrorHandler;

class Maildir extends AbstractStorage
{
    /**
     * used message class, change it in an extended class to extend the returned message class
     * @var string
     */
    protected $messageClass = '\Zend\Mail\Storage\Message\File';

    /**
     * data of found message files in maildir dir
     * @var array
     */
    protected $files = array();

    /**
     * known flag chars in filenames
     *
     * This list has to be in alphabetical order for setFlags()
     *
     * @var array
     */
    protected static $knownFlags = array('D' => Mail\Storage::FLAG_DRAFT,
                                          'F' => Mail\Storage::FLAG_FLAGGED,
                                          'P' => Mail\Storage::FLAG_PASSED,
                                          'R' => Mail\Storage::FLAG_ANSWERED,
                                          'S' => Mail\Storage::FLAG_SEEN,
                                          'T' => Mail\Storage::FLAG_DELETED);

    // TODO: getFlags($id) for fast access if headers are not needed (i.e. just setting flags)?

    /**
     * Count messages all messages in current box
     *
     * @param mixed $flags
     * @return int number of messages
     */
    public function countMessages($flags = null)
    {
        if ($flags === null) {
            return count($this->files);
        }

        $count = 0;
        if (!is_array($flags)) {
            foreach ($this->files as $file) {
                if (isset($file['flaglookup'][$flags])) {
                    ++$count;
                }
            }
            return $count;
        }

        $flags = array_flip($flags);
           foreach ($this->files as $file) {
               foreach ($flags as $flag => $v) {
                   if (!isset($file['flaglookup'][$flag])) {
                       continue 2;
                   }
               }
               ++$count;
           }
           return $count;
    }

    /**
     * Get one or all fields from file structure. Also checks if message is valid
     *
     * @param  int         $id    message number
     * @param  string|null $field wanted field
     * @throws Exception\InvalidArgumentException
     * @return string|array wanted field or all fields as array
     */
    protected function _getFileData($id, $field = null)
    {
        if (!isset($this->files[$id - 1])) {
            throw new Exception\InvalidArgumentException('id does not exist');
        }

        if (!$field) {
            return $this->files[$id - 1];
        }

        if (!isset($this->files[$id - 1][$field])) {
            throw new Exception\InvalidArgumentException('field does not exist');
        }

        return $this->files[$id - 1][$field];
    }

    /**
     * Get a list of messages with number and size
     *
     * @param  int|null $id number of message or null for all messages
     * @return int|array size of given message of list with all messages as array(num => size)
     */
    public function getSize($id = null)
    {
        if ($id !== null) {
            $filedata = $this->_getFileData($id);
            return isset($filedata['size']) ? $filedata['size'] : filesize($filedata['filename']);
        }

        $result = array();
        foreach ($this->files as $num => $data) {
            $result[$num + 1] = isset($data['size']) ? $data['size'] : filesize($data['filename']);
        }

        return $result;
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
            return new $this->messageClass(array('file'  => $this->_getFileData($id, 'filename'),
                                                  'flags' => $this->_getFileData($id, 'flags')));
        }

        return new $this->messageClass(array('handler' => $this, 'id' => $id, 'headers' => $this->getRawHeader($id),
                                              'flags'   => $this->_getFileData($id, 'flags')));
    }

    /*
     * Get raw header of message or part
     *
     * @param  int               $id       number of message
     * @param  null|array|string $part     path to part or null for message header
     * @param  int               $topLines include this many lines with header (after an empty line)
     * @throws Exception\RuntimeException
     * @return string raw header
     */
    public function getRawHeader($id, $part = null, $topLines = 0)
    {
        if ($part !== null) {
            // TODO: implement
            throw new Exception\RuntimeException('not implemented');
        }

        $fh = fopen($this->_getFileData($id, 'filename'), 'r');

        $content = '';
        while (!feof($fh)) {
            $line = fgets($fh);
            if (!trim($line)) {
                break;
            }
            $content .= $line;
        }

        fclose($fh);
        return $content;
    }

    /*
     * Get raw content of message or part
     *
     * @param  int               $id   number of message
     * @param  null|array|string $part path to part or null for message content
     * @throws Exception\RuntimeException
     * @return string raw content
     */
    public function getRawContent($id, $part = null)
    {
        if ($part !== null) {
            // TODO: implement
            throw new Exception\RuntimeException('not implemented');
        }

        $fh = fopen($this->_getFileData($id, 'filename'), 'r');

        while (!feof($fh)) {
            $line = fgets($fh);
            if (!trim($line)) {
                break;
            }
        }

        $content = stream_get_contents($fh);
        fclose($fh);
        return $content;
    }

    /**
     * Create instance with parameters
     * Supported parameters are:
     *   - dirname dirname of mbox file
     *
     * @param  $params array mail reader specific parameters
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object) $params;
        }

        if (!isset($params->dirname) || !is_dir($params->dirname)) {
            throw new Exception\InvalidArgumentException('no valid dirname given in params');
        }

        if (!$this->_isMaildir($params->dirname)) {
            throw new Exception\InvalidArgumentException('invalid maildir given');
        }

        $this->has['top'] = true;
        $this->has['flags'] = true;
        $this->_openMaildir($params->dirname);
    }

    /**
     * check if a given dir is a valid maildir
     *
     * @param string $dirname name of dir
     * @return bool dir is valid maildir
     */
    protected function _isMaildir($dirname)
    {
        if (file_exists($dirname . '/new') && !is_dir($dirname . '/new')) {
            return false;
        }
        if (file_exists($dirname . '/tmp') && !is_dir($dirname . '/tmp')) {
            return false;
        }
        return is_dir($dirname . '/cur');
    }

    /**
     * open given dir as current maildir
     *
     * @param string $dirname name of maildir
     * @throws Exception\RuntimeException
     */
    protected function _openMaildir($dirname)
    {
        if ($this->files) {
            $this->close();
        }

        ErrorHandler::start(E_WARNING);
        $dh    = opendir($dirname . '/cur/');
        $error = ErrorHandler::stop();
        if (!$dh) {
            throw new Exception\RuntimeException('cannot open maildir', 0, $error);
        }
        $this->_getMaildirFiles($dh, $dirname . '/cur/');
        closedir($dh);

        ErrorHandler::start(E_WARNING);
        $dh    = opendir($dirname . '/new/');
        $error = ErrorHandler::stop();
        if ($dh) {
            $this->_getMaildirFiles($dh, $dirname . '/new/', array(Mail\Storage::FLAG_RECENT));
            closedir($dh);
        } elseif (file_exists($dirname . '/new/')) {
            throw new Exception\RuntimeException('cannot read recent mails in maildir', 0, $error);
        }
    }

    /**
     * find all files in opened dir handle and add to maildir files
     *
     * @param resource $dh            dir handle used for search
     * @param string   $dirname       dirname of dir in $dh
     * @param array    $defaultFlags default flags for given dir
     */
    protected function _getMaildirFiles($dh, $dirname, $defaultFlags = array())
    {
        while (($entry = readdir($dh)) !== false) {
            if ($entry[0] == '.' || !is_file($dirname . $entry)) {
                continue;
            }

            ErrorHandler::start(E_NOTICE);
            list($uniq, $info) = explode(':', $entry, 2);
            list(, $size) = explode(',', $uniq, 2);
            ErrorHandler::stop();
            if ($size && $size[0] == 'S' && $size[1] == '=') {
                $size = substr($size, 2);
            }
            if (!ctype_digit($size)) {
                $size = null;
            }

            ErrorHandler::start(E_NOTICE);
            list($version, $flags) = explode(',', $info, 2);
            ErrorHandler::stop();
            if ($version != 2) {
                $flags = '';
            }

            $namedFlags = $defaultFlags;
            $length = strlen($flags);
            for ($i = 0; $i < $length; ++$i) {
                $flag = $flags[$i];
                $namedFlags[$flag] = isset(static::$knownFlags[$flag]) ? static::$knownFlags[$flag] : $flag;
            }

            $data = array('uniq'       => $uniq,
                          'flags'      => $namedFlags,
                          'flaglookup' => array_flip($namedFlags),
                          'filename'   => $dirname . $entry);
            if ($size !== null) {
                $data['size'] = (int) $size;
            }
            $this->files[] = $data;
        }
    }


    /**
     * Close resource for mail lib. If you need to control, when the resource
     * is closed. Otherwise the destructor would call this.
     *
     */
    public function close()
    {
        $this->files = array();
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
        throw new Exception\RuntimeException('maildir is (currently) read-only');
    }

    /**
     * get unique id for one or all messages
     *
     * if storage does not support unique ids it's the same as the message number
     *
     * @param int|null $id message number
     * @return array|string message number for given message or all messages as array
     */
    public function getUniqueId($id = null)
    {
        if ($id) {
            return $this->_getFileData($id, 'uniq');
        }

        $ids = array();
        foreach ($this->files as $num => $file) {
            $ids[$num + 1] = $file['uniq'];
        }
        return $ids;
    }

    /**
     * get a message number from a unique id
     *
     * I.e. if you have a webmailer that supports deleting messages you should use unique ids
     * as parameter and use this method to translate it to message number right before calling removeMessage()
     *
     * @param string $id unique id
     * @throws Exception\InvalidArgumentException
     * @return int message number
     */
    public function getNumberByUniqueId($id)
    {
        foreach ($this->files as $num => $file) {
            if ($file['uniq'] == $id) {
                return $num + 1;
            }
        }

        throw new Exception\InvalidArgumentException('unique id not found');
    }
}
