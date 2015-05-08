<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\File\Transfer\Adapter;

use Zend\File\Transfer;
use Zend\File\Transfer\Exception;
use Zend\ProgressBar;
use Zend\ProgressBar\Adapter;

/**
 * File transfer adapter class for the HTTP protocol
 *
 */
class Http extends AbstractAdapter
{
    protected static $callbackApc            = 'apc_fetch';
    protected static $callbackUploadProgress = 'uploadprogress_get_info';

    /**
     * Constructor for Http File Transfers
     *
     * @param  array $options OPTIONAL Options to set
     * @throws Exception\PhpEnvironmentException if file uploads are not allowed
     */
    public function __construct($options = array())
    {
        if (ini_get('file_uploads') == false) {
            throw new Exception\PhpEnvironmentException('File uploads are not allowed in your php config!');
        }

        $this->setOptions($options);
        $this->prepareFiles();
        $this->addValidator('Upload', false, $this->files);
    }

    /**
     * Sets a validator for the class, erasing all previous set
     *
     * @param  array        $validators Validator to set
     * @param  string|array $files      Files to limit this validator to
     * @return AbstractAdapter
     */
    public function setValidators(array $validators, $files = null)
    {
        $this->clearValidators();
        return $this->addValidators($validators, $files);
    }

    /**
     * Remove an individual validator
     *
     * @param  string $name
     * @return AbstractAdapter
     */
    public function removeValidator($name)
    {
        if ($name == 'Upload') {
            return $this;
        }

        return parent::removeValidator($name);
    }

    /**
     * Clear the validators
     *
     * @return AbstractAdapter
     */
    public function clearValidators()
    {
        parent::clearValidators();
        $this->addValidator('Upload', false, $this->files);

        return $this;
    }

    /**
     * Send the file to the client (Download)
     *
     * @param  string|array $options Options for the file(s) to send
     * @return void
     * @throws Exception\BadMethodCallException Not implemented
     */
    public function send($options = null)
    {
        throw new Exception\BadMethodCallException('Method not implemented');
    }

    /**
     * Checks if the files are valid
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool True if all checks are valid
     */
    public function isValid($files = null)
    {
        // Workaround for WebServer not conforming HTTP and omitting CONTENT_LENGTH
        $content = 0;
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $content = $_SERVER['CONTENT_LENGTH'];
        } elseif (!empty($_POST)) {
            $content = serialize($_POST);
        }

        // Workaround for a PHP error returning empty $_FILES when form data exceeds php settings
        if (empty($this->files) && ($content > 0)) {
            if (is_array($files)) {
                $files = current($files);
            }

            $temp = array($files => array(
                'name'  => $files,
                'error' => 1));
            $validator = $this->validators['Zend\Validator\File\Upload'];
            $validator->setTranslator($this->getTranslator())
                      ->setFiles($temp)
                      ->isValid($files, null);
            $this->messages += $validator->getMessages();
            return false;
        }

        return parent::isValid($files);
    }

    /**
     * Receive the file from the client (Upload)
     *
     * @param  string|array $files (Optional) Files to receive
     * @return bool
     */
    public function receive($files = null)
    {
        if (!$this->isValid($files)) {
            return false;
        }

        $check = $this->getFiles($files);
        foreach ($check as $file => $content) {
            if (!$content['received']) {
                $directory   = '';
                $destination = $this->getDestination($file);
                if ($destination !== null) {
                    $directory = $destination . DIRECTORY_SEPARATOR;
                }

                $filename = $directory . $content['name'];
                $rename   = $this->getFilter('Rename');
                if ($rename !== null) {
                    $tmp = $rename->getNewName($content['tmp_name']);
                    if ($tmp != $content['tmp_name']) {
                        $filename = $tmp;
                    }

                    if (dirname($filename) == '.') {
                        $filename = $directory . $filename;
                    }

                    $key = array_search(get_class($rename), $this->files[$file]['filters']);
                    unset($this->files[$file]['filters'][$key]);
                }

                // Should never return false when it's tested by the upload validator
                if (!move_uploaded_file($content['tmp_name'], $filename)) {
                    if ($content['options']['ignoreNoFile']) {
                        $this->files[$file]['received'] = true;
                        $this->files[$file]['filtered'] = true;
                        continue;
                    }

                    $this->files[$file]['received'] = false;
                    return false;
                }

                if ($rename !== null) {
                    $this->files[$file]['destination'] = dirname($filename);
                    $this->files[$file]['name']        = basename($filename);
                }

                $this->files[$file]['tmp_name'] = $filename;
                $this->files[$file]['received'] = true;
            }

            if (!$content['filtered']) {
                if (!$this->filter($file)) {
                    $this->files[$file]['filtered'] = false;
                    return false;
                }

                $this->files[$file]['filtered'] = true;
            }
        }

        return true;
    }

    /**
     * Checks if the file was already sent
     *
     * @param  string|array $files Files to check
     * @return bool
     * @throws Exception\BadMethodCallException Not implemented
     */
    public function isSent($files = null)
    {
        throw new Exception\BadMethodCallException('Method not implemented');
    }

    /**
     * Checks if the file was already received
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool
     */
    public function isReceived($files = null)
    {
        $files = $this->getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $content) {
            if ($content['received'] !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the file was already filtered
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool
     */
    public function isFiltered($files = null)
    {
        $files = $this->getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $content) {
            if ($content['filtered'] !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Has a file been uploaded ?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isUploaded($files = null)
    {
        $files = $this->getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $file) {
            if (empty($file['name'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the actual progress of file up-/downloads
     *
     * @param  string|array $id The upload to get the progress for
     * @return array|null
     * @throws Exception\PhpEnvironmentException whether APC nor UploadProgress extension installed
     * @throws Exception\RuntimeException
     */
    public static function getProgress($id = null)
    {
        if (!static::isApcAvailable() && !static::isUploadProgressAvailable()) {
            throw new Exception\PhpEnvironmentException('Neither APC nor UploadProgress extension installed');
        }

        $session = 'Zend\File\Transfer\Adapter\Http\ProgressBar';
        $status  = array(
            'total'    => 0,
            'current'  => 0,
            'rate'     => 0,
            'message'  => '',
            'done'     => false
        );

        if (is_array($id)) {
            if (isset($id['progress'])) {
                $adapter = $id['progress'];
            }

            if (isset($id['session'])) {
                $session = $id['session'];
            }

            if (isset($id['id'])) {
                $id = $id['id'];
            } else {
                unset($id);
            }
        }

        if (!empty($id) && (($id instanceof Adapter\AbstractAdapter) || ($id instanceof ProgressBar\ProgressBar))) {
            $adapter = $id;
            unset($id);
        }

        if (empty($id)) {
            if (!isset($_GET['progress_key'])) {
                $status['message'] = 'No upload in progress';
                $status['done']    = true;
            } else {
                $id = $_GET['progress_key'];
            }
        }

        if (!empty($id)) {
            if (static::isApcAvailable()) {

                $call = call_user_func(static::$callbackApc, ini_get('apc.rfc1867_prefix') . $id);
                if (is_array($call)) {
                    $status = $call + $status;
                }
            } elseif (static::isUploadProgressAvailable()) {
                $call = call_user_func(static::$callbackUploadProgress, $id);
                if (is_array($call)) {
                    $status = $call + $status;
                    $status['total']   = $status['bytes_total'];
                    $status['current'] = $status['bytes_uploaded'];
                    $status['rate']    = $status['speed_average'];
                    if ($status['total'] == $status['current']) {
                        $status['done'] = true;
                    }
                }
            }

            if (!is_array($call)) {
                $status['done']    = true;
                $status['message'] = 'Failure while retrieving the upload progress';
            } elseif (!empty($status['cancel_upload'])) {
                $status['done']    = true;
                $status['message'] = 'The upload has been canceled';
            } else {
                $status['message'] = static::toByteString($status['current']) . " - " . static::toByteString($status['total']);
            }

            $status['id'] = $id;
        }

        if (isset($adapter) && isset($status['id'])) {
            if ($adapter instanceof Adapter\AbstractAdapter) {
                $adapter = new ProgressBar\ProgressBar($adapter, 0, $status['total'], $session);
            }

            if (!($adapter instanceof ProgressBar\ProgressBar)) {
                throw new Exception\RuntimeException('Unknown Adapter given');
            }

            if ($status['done']) {
                $adapter->finish();
            } else {
                $adapter->update($status['current'], $status['message']);
            }

            $status['progress'] = $adapter;
        }

        return $status;
    }

    /**
     * Checks the APC extension for progress information
     *
     * @return bool
     */
    public static function isApcAvailable()
    {
        return (bool) ini_get('apc.enabled') && (bool) ini_get('apc.rfc1867') && is_callable(static::$callbackApc);
    }

    /**
     * Checks the UploadProgress extension for progress information
     *
     * @return bool
     */
    public static function isUploadProgressAvailable()
    {
        return is_callable(static::$callbackUploadProgress);
    }

    /**
     * Prepare the $_FILES array to match the internal syntax of one file per entry
     *
     * @return Http
     */
    protected function prepareFiles()
    {
        $this->files = array();
        foreach ($_FILES as $form => $content) {
            if (is_array($content['name'])) {
                foreach ($content as $param => $file) {
                    foreach ($file as $number => $target) {
                        $this->files[$form . '_' . $number . '_'][$param]      = $target;
                        $this->files[$form]['multifiles'][$number] = $form . '_' . $number . '_';
                    }
                }

                $this->files[$form]['name'] = $form;
                foreach ($this->files[$form]['multifiles'] as $key => $value) {
                    $this->files[$value]['options']   = $this->options;
                    $this->files[$value]['validated'] = false;
                    $this->files[$value]['received']  = false;
                    $this->files[$value]['filtered']  = false;

                    $mimetype = $this->detectMimeType($this->files[$value]);
                    $this->files[$value]['type'] = $mimetype;

                    $filesize = $this->detectFileSize($this->files[$value]);
                    $this->files[$value]['size'] = $filesize;

                    if ($this->options['detectInfos']) {
                        $_FILES[$form]['type'][$key] = $mimetype;
                        $_FILES[$form]['size'][$key] = $filesize;
                    }
                }
            } else {
                $this->files[$form]              = $content;
                $this->files[$form]['options']   = $this->options;
                $this->files[$form]['validated'] = false;
                $this->files[$form]['received']  = false;
                $this->files[$form]['filtered']  = false;

                $mimetype = $this->detectMimeType($this->files[$form]);
                $this->files[$form]['type'] = $mimetype;

                $filesize = $this->detectFileSize($this->files[$form]);
                $this->files[$form]['size'] = $filesize;

                if ($this->options['detectInfos']) {
                    $_FILES[$form]['type'] = $mimetype;
                    $_FILES[$form]['size'] = $filesize;
                }
            }
        }

        return $this;
    }
}
