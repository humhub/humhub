<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Transport;

use Zend\Mail\Message;

/**
 * File transport
 *
 * Class for saving outgoing emails in filesystem
 */
class File implements TransportInterface
{
    /**
     * @var FileOptions
     */
    protected $options;

    /**
     * Last file written to
     *
     * @var string
     */
    protected $lastFile;

    /**
     * Constructor
     *
     * @param  null|FileOptions $options OPTIONAL (Default: null)
     */
    public function __construct(FileOptions $options = null)
    {
        if (!$options instanceof FileOptions) {
            $options = new FileOptions();
        }
        $this->setOptions($options);
    }

    /**
     * Sets options
     *
     * @param  FileOptions $options
     */
    public function setOptions(FileOptions $options)
    {
        $this->options = $options;
    }

    /**
     * Saves e-mail message to a file
     *
     * @param Message $message
     * @throws Exception\RuntimeException on not writable target directory or
     * on file_put_contents() failure
     */
    public function send(Message $message)
    {
        $options  = $this->options;
        $filename = call_user_func($options->getCallback(), $this);
        $file     = $options->getPath() . DIRECTORY_SEPARATOR . $filename;
        $email    = $message->toString();

        if (false === file_put_contents($file, $email)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to write mail to file (directory "%s")',
                $options->getPath()
            ));
        }

        $this->lastFile = $file;
    }

    /**
     * Get the name of the last file written to
     *
     * @return string
     */
    public function getLastFile()
    {
        return $this->lastFile;
    }
}
