<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Transport;

use Zend\Mail\Address;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Protocol;
use Zend\Mail\Protocol\Exception as ProtocolException;

/**
 * SMTP connection object
 *
 * Loads an instance of Zend\Mail\Protocol\Smtp and forwards smtp transactions
 */
class Smtp implements TransportInterface
{
    /**
     * @var SmtpOptions
     */
    protected $options;

    /**
     * @var Protocol\Smtp
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $autoDisconnect = true;

    /**
     * @var Protocol\SmtpPluginManager
     */
    protected $plugins;

    /**
     * Constructor.
     *
     * @param  SmtpOptions $options Optional
     */
    public function __construct(SmtpOptions $options = null)
    {
        if (!$options instanceof SmtpOptions) {
            $options = new SmtpOptions();
        }
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param  SmtpOptions $options
     * @return Smtp
     */
    public function setOptions(SmtpOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return SmtpOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set plugin manager for obtaining SMTP protocol connection
     *
     * @param  Protocol\SmtpPluginManager $plugins
     * @throws Exception\InvalidArgumentException
     * @return Smtp
     */
    public function setPluginManager(Protocol\SmtpPluginManager $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * Get plugin manager for loading SMTP protocol connection
     *
     * @return Protocol\SmtpPluginManager
     */
    public function getPluginManager()
    {
        if (null === $this->plugins) {
            $this->setPluginManager(new Protocol\SmtpPluginManager());
        }
        return $this->plugins;
    }

    /**
     * Set the automatic disconnection when destruct
     *
     * @param  bool $flag
     * @return Smtp
     */
    public function setAutoDisconnect($flag)
    {
        $this->autoDisconnect = (bool) $flag;
        return $this;
    }

    /**
     * Get the automatic disconnection value
     *
     * @return bool
     */
    public function getAutoDisconnect()
    {
        return $this->autoDisconnect;
    }

    /**
     * Return an SMTP connection
     *
     * @param  string $name
     * @param  array|null $options
     * @return Protocol\Smtp
     */
    public function plugin($name, array $options = null)
    {
        return $this->getPluginManager()->get($name, $options);
    }

    /**
     * Class destructor to ensure all open connections are closed
     */
    public function __destruct()
    {
        if ($this->connection instanceof Protocol\Smtp) {
            try {
                $this->connection->quit();
            } catch (ProtocolException\ExceptionInterface $e) {
                // ignore
            }
            if ($this->autoDisconnect) {
                $this->connection->disconnect();
            }
        }
    }

    /**
     * Sets the connection protocol instance
     *
     * @param Protocol\AbstractProtocol $connection
     */
    public function setConnection(Protocol\AbstractProtocol $connection)
    {
        $this->connection = $connection;
    }


    /**
     * Gets the connection protocol instance
     *
     * @return Protocol\Smtp
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Disconnect the connection protocol instance
     *
     * @return void
     */
    public function disconnect()
    {
        if (!empty($this->connection) && ($this->connection instanceof Protocol\Smtp)) {
            $this->connection->disconnect();
        }
    }

    /**
     * Send an email via the SMTP connection protocol
     *
     * The connection via the protocol adapter is made just-in-time to allow a
     * developer to add a custom adapter if required before mail is sent.
     *
     * @param Message $message
     * @throws Exception\RuntimeException
     */
    public function send(Message $message)
    {
        // If sending multiple messages per session use existing adapter
        $connection = $this->getConnection();

        if (!($connection instanceof Protocol\Smtp) || !$connection->hasSession()) {
            $connection = $this->connect();
        } else {
            // Reset connection to ensure reliable transaction
            $connection->rset();
        }

        // Prepare message
        $from       = $this->prepareFromAddress($message);
        $recipients = $this->prepareRecipients($message);
        $headers    = $this->prepareHeaders($message);
        $body       = $this->prepareBody($message);

        if ((count($recipients) == 0) && (!empty($headers) || !empty($body))) {
            throw new Exception\RuntimeException(  // Per RFC 2821 3.3 (page 18)
                sprintf(
                    '%s transport expects at least one recipient if the message has at least one header or body',
                    __CLASS__
                ));
        }

        // Set sender email address
        $connection->mail($from);

        // Set recipient forward paths
        foreach ($recipients as $recipient) {
            $connection->rcpt($recipient);
        }

        // Issue DATA command to client
        $connection->data($headers . Headers::EOL . $body);
    }

    /**
     * Retrieve email address for envelope FROM
     *
     * @param  Message $message
     * @throws Exception\RuntimeException
     * @return string
     */
    protected function prepareFromAddress(Message $message)
    {
        $sender = $message->getSender();
        if ($sender instanceof Address\AddressInterface) {
            return $sender->getEmail();
        }

        $from = $message->getFrom();
        if (!count($from)) { // Per RFC 2822 3.6
            throw new Exception\RuntimeException(sprintf(
                '%s transport expects either a Sender or at least one From address in the Message; none provided',
                __CLASS__
            ));
        }

        $from->rewind();
        $sender = $from->current();
        return $sender->getEmail();
    }

    /**
     * Prepare array of email address recipients
     *
     * @param  Message $message
     * @return array
     */
    protected function prepareRecipients(Message $message)
    {
        $recipients = array();
        foreach ($message->getTo() as $address) {
            $recipients[] = $address->getEmail();
        }
        foreach ($message->getCc() as $address) {
            $recipients[] = $address->getEmail();
        }
        foreach ($message->getBcc() as $address) {
            $recipients[] = $address->getEmail();
        }
        $recipients = array_unique($recipients);
        return $recipients;
    }

    /**
     * Prepare header string from message
     *
     * @param  Message $message
     * @return string
     */
    protected function prepareHeaders(Message $message)
    {
        $headers = clone $message->getHeaders();
        $headers->removeHeader('Bcc');
        return $headers->toString();
    }

    /**
     * Prepare body string from message
     *
     * @param  Message $message
     * @return string
     */
    protected function prepareBody(Message $message)
    {
        return $message->getBodyText();
    }

    /**
     * Lazy load the connection
     *
     * @return Protocol\Smtp
     */
    protected function lazyLoadConnection()
    {
        // Check if authentication is required and determine required class
        $options          = $this->getOptions();
        $config           = $options->getConnectionConfig();
        $config['host']   = $options->getHost();
        $config['port']   = $options->getPort();
        $connection       = $this->plugin($options->getConnectionClass(), $config);
        $this->connection = $connection;

        return $this->connect();
    }

    /**
     * Connect the connection, and pass it helo
     *
     * @return Protocol\Smtp
     */
    protected function connect()
    {
        if (!$this->connection instanceof Protocol\Smtp) {
            return $this->lazyLoadConnection();
        }

        $this->connection->connect();
        $this->connection->helo($this->getOptions()->getName());

        return $this->connection;
    }
}
