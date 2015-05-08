<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Header;

use Zend\Loader\PluginClassLoader;

/**
 * Plugin Class Loader implementation for HTTP headers
 */
class HeaderLoader extends PluginClassLoader
{
    /**
     * @var array Pre-aliased Header plugins
     */
    protected $plugins = array(
        'bcc'                       => 'Zend\Mail\Header\Bcc',
        'cc'                        => 'Zend\Mail\Header\Cc',
        'contenttype'               => 'Zend\Mail\Header\ContentType',
        'content_type'              => 'Zend\Mail\Header\ContentType',
        'content-type'              => 'Zend\Mail\Header\ContentType',
        'contenttransferencoding'   => 'Zend\Mail\Header\ContentTransferEncoding',
        'content_transfer_encoding' => 'Zend\Mail\Header\ContentTransferEncoding',
        'content-transfer-encoding' => 'Zend\Mail\Header\ContentTransferEncoding',
        'date'                      => 'Zend\Mail\Header\Date',
        'from'                      => 'Zend\Mail\Header\From',
        'message-id'                => 'Zend\Mail\Header\MessageId',
        'mimeversion'               => 'Zend\Mail\Header\MimeVersion',
        'mime_version'              => 'Zend\Mail\Header\MimeVersion',
        'mime-version'              => 'Zend\Mail\Header\MimeVersion',
        'received'                  => 'Zend\Mail\Header\Received',
        'replyto'                   => 'Zend\Mail\Header\ReplyTo',
        'reply_to'                  => 'Zend\Mail\Header\ReplyTo',
        'reply-to'                  => 'Zend\Mail\Header\ReplyTo',
        'sender'                    => 'Zend\Mail\Header\Sender',
        'subject'                   => 'Zend\Mail\Header\Subject',
        'to'                        => 'Zend\Mail\Header\To',
    );
}
