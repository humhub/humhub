<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ProgressBar\Upload;

use Traversable;
use Zend\ProgressBar\Adapter\AbstractAdapter as AbstractProgressAdapter;
use Zend\ProgressBar\Exception;
use Zend\ProgressBar\ProgressBar;
use Zend\Stdlib\ArrayUtils;

/**
 * Abstract class for Upload Progress Handlers
 */
abstract class AbstractUploadHandler implements UploadHandlerInterface
{
    /**
     * @var string
     */
    protected $sessionNamespace = 'Zend\ProgressBar\Upload\AbstractUploadHandler';

    /**
     * @var AbstractProgressAdapter|ProgressBar
     */
    protected $progressAdapter;

    /**
     * @param  array|Traversable $options Optional options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = array())
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options for a upload handler. Accepted options are:
     * - session_namespace: session namespace for upload progress
     * - progress_adapter: progressbar adapter to use for updating progress
     *
     * @param  array|Traversable $options
     * @return AbstractUploadHandler
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }

        if (isset($options['session_namespace'])) {
            $this->setSessionNamespace($options['session_namespace']);
        }
        if (isset($options['progress_adapter'])) {
            $this->setProgressAdapter($options['progress_adapter']);
        }

        return $this;
    }

    /**
     * @param  string $sessionNamespace
     * @return AbstractUploadHandler|UploadHandlerInterface
     */
    public function setSessionNamespace($sessionNamespace)
    {
        $this->sessionNamespace = $sessionNamespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionNamespace()
    {
        return $this->sessionNamespace;
    }

    /**
     * @param  AbstractProgressAdapter|ProgressBar $progressAdapter
     * @return AbstractUploadHandler|UploadHandlerInterface
     */
    public function setProgressAdapter($progressAdapter)
    {
        $this->progressAdapter = $progressAdapter;
        return $this;
    }

    /**
     * @return AbstractProgressAdapter|ProgressBar
     */
    public function getProgressAdapter()
    {
        return $this->progressAdapter;
    }

    /**
     * @param  string $id
     * @return array
     */
    public function getProgress($id)
    {
        $status  = array(
            'total'    => 0,
            'current'  => 0,
            'rate'     => 0,
            'message'  => 'No upload in progress',
            'done'     => true
        );
        if (empty($id)) {
            return $status;
        }

        $newStatus = $this->getUploadProgress($id);
        if (false === $newStatus) {
            return $status;
        }
        $status = $newStatus;
        if ('' === $status['message']) {
            $status['message'] = $this->toByteString($status['current']) .
                " - " . $this->toByteString($status['total']);
        }
        $status['id'] = $id;

        $adapter = $this->getProgressAdapter();
        if (isset($adapter)) {
            if ($adapter instanceof AbstractProgressAdapter) {
                $adapter = new ProgressBar(
                    $adapter, 0, $status['total'], $this->getSessionNamespace()
                );
                $this->setProgressAdapter($adapter);
            }

            if (!$adapter instanceof ProgressBar) {
                throw new Exception\RuntimeException('Unknown Adapter type given');
            }

            if ($status['done']) {
                $adapter->finish();
            } else {
                $adapter->update($status['current'], $status['message']);
            }
        }

        return $status;
    }

    /**
     * @param  string $id
     * @return array|bool
     */
    abstract protected function getUploadProgress($id);

    /**
     * Returns the formatted size
     *
     * @param  int $size
     * @return string
     */
    protected function toByteString($size)
    {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $sizes[$i];
    }
}
