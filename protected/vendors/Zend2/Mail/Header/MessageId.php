<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Header;


class MessageId implements HeaderInterface
{
    /**
     * @var string
     */
    protected $messageId;


    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'message-id') {
            throw new Exception\InvalidArgumentException('Invalid header line for Message-ID string');
        }

        $header = new static();
        $header->setId($value);

        return $header;
    }

    public function getFieldName()
    {
        return 'Message-ID';
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        return $this->messageId;
    }

    public function setEncoding($encoding)
    {
        // This header must be always in US-ASCII
        return $this;
    }

    public function getEncoding()
    {
        return 'ASCII';
    }

    public function toString()
    {
        return 'Message-ID: ' . $this->getFieldValue();
    }

    /**
     * Set the message id
     *
     * @param string|null $id
     * @return MessageId
     */
    public function setId($id = null)
    {
        if ($id === null) {
            $id = $this->createMessageId();
        }

        $id = sprintf('<%s>', $id);
        $this->messageId = $id;
        return $this;
    }

    /**
     * Retrieve the message id
     *
     * @return string
     */
    public function getId()
    {
        return $this->messageId;
    }

    /**
     * Creates the Message-ID
     *
     * @return string
     */
    public function createMessageId()
    {
        $time = time();

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $user = $_SERVER['REMOTE_ADDR'];
        } else {
            $user = getmypid();
        }

        $rand = mt_rand();

        if (isset($_SERVER["SERVER_NAME"])) {
            $hostName = $_SERVER["SERVER_NAME"];
        } else {
            $hostName = php_uname('n');
        }

        return sha1($time . $user . $rand) . '@' . $hostName;
    }
}
