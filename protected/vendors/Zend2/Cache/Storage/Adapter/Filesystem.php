<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Exception as BaseException;
use GlobIterator;
use stdClass;
use Zend\Cache\Exception;
use Zend\Cache\Storage;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\ClearByPrefixInterface;
use Zend\Cache\Storage\ClearExpiredInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\IterableInterface;
use Zend\Cache\Storage\OptimizableInterface;
use Zend\Cache\Storage\TaggableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;
use Zend\Stdlib\ErrorHandler;

class Filesystem extends AbstractAdapter implements
    AvailableSpaceCapableInterface,
    ClearByNamespaceInterface,
    ClearByPrefixInterface,
    ClearExpiredInterface,
    FlushableInterface,
    IterableInterface,
    OptimizableInterface,
    TaggableInterface,
    TotalSpaceCapableInterface
{

    /**
     * Buffered total space in bytes
     *
     * @var null|int|float
     */
    protected $totalSpace;

    /**
     * An identity for the last filespec
     * (cache directory + namespace prefix + key + directory level)
     *
     * @var string
     */
    protected $lastFileSpecId = '';

    /**
     * The last used filespec
     *
     * @var string
     */
    protected $lastFileSpec = '';

    /**
     * Set options.
     *
     * @param  array|\Traversable|FilesystemOptions $options
     * @return Filesystem
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof FilesystemOptions) {
            $options = new FilesystemOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @return FilesystemOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new FilesystemOptions());
        }
        return $this->options;
    }

    /* FlushableInterface */

    /**
     * Flush the whole storage
     *
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function flush()
    {
        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $dir   = $this->getOptions()->getCacheDir();
        $clearFolder = null;
        $clearFolder = function ($dir) use (& $clearFolder, $flags) {
            $it = new GlobIterator($dir . DIRECTORY_SEPARATOR . '*', $flags);
            foreach ($it as $pathname) {
                if ($it->isDir()) {
                    $clearFolder($pathname);
                    rmdir($pathname);
                } else {
                    unlink($pathname);
                }
            }
        };

        ErrorHandler::start();
        $clearFolder($dir);
        $error = ErrorHandler::stop();
        if ($error) {
            throw new Exception\RuntimeException("Flushing directory '{$dir}' failed", 0, $error);
        }

        return true;
    }

    /* ClearExpiredInterface */

    /**
     * Remove expired items
     *
     * @return bool
     */
    public function clearExpired()
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_FILEINFO;
        $path  = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*.dat';
        $glob = new GlobIterator($path, $flags);
        $time = time();
        $ttl  = $options->getTtl();

        ErrorHandler::start();
        foreach ($glob as $entry) {
            $mtime = $entry->getMTime();
            if ($time >= $mtime + $ttl) {
                $pathname = $entry->getPathname();
                unlink($pathname);

                $tagPathname = substr($pathname, 0, -4) . '.tag';
                if (file_exists($tagPathname)) {
                    unlink($tagPathname);
                }
            }
        }
        $error = ErrorHandler::stop();
        if ($error) {
            throw new Exception\RuntimeException("Failed to clear expired items", 0, $error);
        }

        return true;
    }

    /* ClearByNamespaceInterface */

    /**
     * Remove items by given namespace
     *
     * @param string $namespace
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function clearByNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($namespace === '') {
            throw new Exception\InvalidArgumentException('No namespace given');
        }

        $options = $this->getOptions();
        $prefix  = $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $path = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*';
        $glob = new GlobIterator($path, $flags);

        ErrorHandler::start();
        foreach ($glob as $pathname) {
            unlink($pathname);
        }
        $error = ErrorHandler::stop();
        if ($error) {
            throw new Exception\RuntimeException("Failed to remove files of '{$path}'", 0, $error);
        }

        return true;
    }

    /* ClearByPrefixInterface */

    /**
     * Remove items matching given prefix
     *
     * @param string $prefix
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function clearByPrefix($prefix)
    {
        $prefix = (string) $prefix;
        if ($prefix === '') {
            throw new Exception\InvalidArgumentException('No prefix given');
        }

        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $nsPrefix  = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $path = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $nsPrefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $nsPrefix . $prefix . '*';
        $glob = new GlobIterator($path, $flags);

        ErrorHandler::start();
        foreach ($glob as $pathname) {
            unlink($pathname);
        }
        $error = ErrorHandler::stop();
        if ($error) {
            throw new Exception\RuntimeException("Failed to remove files of '{$path}'", 0, $error);
        }

        return true;
    }

    /* TaggableInterface  */

    /**
     * Set tags to an item by given key.
     * An empty array will remove all tags.
     *
     * @param string   $key
     * @param string[] $tags
     * @return bool
     */
    public function setTags($key, array $tags)
    {
        $this->normalizeKey($key);
        if (!$this->internalHasItem($key)) {
            return false;
        }

        $filespec = $this->getFileSpec($key);

        if (!$tags) {
            $this->unlink($filespec . '.tag');
            return true;
        }

        $this->putFileContent($filespec . '.tag', implode("\n", $tags));
        return true;
    }

    /**
     * Get tags of an item by given key
     *
     * @param string $key
     * @return string[]|FALSE
     */
    public function getTags($key)
    {
        $this->normalizeKey($key);
        if (!$this->internalHasItem($key)) {
            return false;
        }

        $filespec = $this->getFileSpec($key);
        $tags     = array();
        if (file_exists($filespec . '.tag')) {
            $tags = explode("\n", $this->getFileContent($filespec . '.tag'));
        }

        return $tags;
    }

    /**
     * Remove items matching given tags.
     *
     * If $disjunction only one of the given tags must match
     * else all given tags must match.
     *
     * @param string[] $tags
     * @param  bool  $disjunction
     * @return bool
     */
    public function clearByTags(array $tags, $disjunction = false)
    {
        if (!$tags) {
            return true;
        }

        $tagCount  = count($tags);
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

        $flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME;
        $path  = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*.tag';
        $glob = new GlobIterator($path, $flags);

        foreach ($glob as $pathname) {
            $diff = array_diff($tags, explode("\n", $this->getFileContent($pathname)));

            $rem  = false;
            if ($disjunction && count($diff) < $tagCount) {
                $rem = true;
            } elseif (!$disjunction && !$diff) {
                $rem = true;
            }

            if ($rem) {
                unlink($pathname);

                $datPathname = substr($pathname, 0, -4) . '.dat';
                if (file_exists($datPathname)) {
                    unlink($datPathname);
                }
            }
        }

        return true;
    }

    /* IterableInterface */

    /**
     * Get the storage iterator
     *
     * @return FilesystemIterator
     */
    public function getIterator()
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $path      = $options->getCacheDir()
            . str_repeat(DIRECTORY_SEPARATOR . $prefix . '*', $options->getDirLevel())
            . DIRECTORY_SEPARATOR . $prefix . '*.dat';
        return new FilesystemIterator($this, $path, $prefix);
    }

    /* OptimizableInterface */

    /**
     * Optimize the storage
     *
     * @return bool
     * @return Exception\RuntimeException
     */
    public function optimize()
    {
        $options = $this->getOptions();
        if ($options->getDirLevel()) {
            $namespace = $options->getNamespace();
            $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();

            // removes only empty directories
            $this->rmDir($options->getCacheDir(), $prefix);
        }
        return true;
    }

    /* TotalSpaceCapableInterface */

    /**
     * Get total space in bytes
     *
     * @throws Exception\RuntimeException
     * @return int|float
     */
    public function getTotalSpace()
    {
        if ($this->totalSpace === null) {
            $path = $this->getOptions()->getCacheDir();

            ErrorHandler::start();
            $total = disk_total_space($path);
            $error = ErrorHandler::stop();
            if ($total === false) {
                throw new Exception\RuntimeException("Can't detect total space of '{$path}'", 0, $error);
            }
            $this->totalSpace = $total;

            // clean total space buffer on change cache_dir
            $events     = $this->getEventManager();
            $handle     = null;
            $totalSpace = & $this->totalSpace;
            $callback   = function ($event) use (& $events, & $handle, & $totalSpace) {
                $params = $event->getParams();
                if (isset($params['cache_dir'])) {
                    $totalSpace = null;
                    $events->detach($handle);
                }
            };
            $handle = $events->attach('option', $callback);
        }

        return $this->totalSpace;
    }

    /* AvailableSpaceCapableInterface */

    /**
     * Get available space in bytes
     *
     * @throws Exception\RuntimeException
     * @return int|float
     */
    public function getAvailableSpace()
    {
        $path = $this->getOptions()->getCacheDir();

        ErrorHandler::start();
        $avail = disk_free_space($path);
        $error = ErrorHandler::stop();
        if ($avail === false) {
            throw new Exception\RuntimeException("Can't detect free space of '{$path}'", 0, $error);
        }

        return $avail;
    }

    /* reading */

    /**
     * Get an item.
     *
     * @param  string  $key
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers getItem.pre(PreEvent)
     * @triggers getItem.post(PostEvent)
     * @triggers getItem.exception(ExceptionEvent)
     */
    public function getItem($key, & $success = null, & $casToken = null)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        $argn = func_num_args();
        if ($argn > 2) {
            return parent::getItem($key, $success, $casToken);
        } elseif ($argn > 1) {
            return parent::getItem($key, $success);
        }

        return parent::getItem($key);
    }

    /**
     * Get multiple items.
     *
     * @param  array $keys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     *
     * @triggers getItems.pre(PreEvent)
     * @triggers getItems.post(PostEvent)
     * @triggers getItems.exception(ExceptionEvent)
     */
    public function getItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::getItems($keys);
    }

    /**
     * Internal method to get an item.
     *
     * @param  string  $normalizedKey
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            $success = false;
            return null;
        }

        try {
            $filespec = $this->getFileSpec($normalizedKey);
            $data     = $this->getFileContent($filespec . '.dat');

            // use filemtime + filesize as CAS token
            if (func_num_args() > 2) {
                $casToken = filemtime($filespec . '.dat') . filesize($filespec . '.dat');
            }
            $success  = true;
            return $data;

        } catch (BaseException $e) {
            $success = false;
            throw $e;
        }
    }

    /**
     * Internal method to get multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $options = $this->getOptions();
        $keys    = $normalizedKeys; // Don't change argument passed by reference
        $result  = array();
        while ($keys) {

            // LOCK_NB if more than one items have to read
            $nonBlocking = count($keys) > 1;
            $wouldblock  = null;

            // read items
            foreach ($keys as $i => $key) {
                if (!$this->internalHasItem($key)) {
                    unset($keys[$i]);
                    continue;
                }

                $filespec = $this->getFileSpec($key);
                $data     = $this->getFileContent($filespec . '.dat', $nonBlocking, $wouldblock);
                if ($nonBlocking && $wouldblock) {
                    continue;
                } else {
                    unset($keys[$i]);
                }

                $result[$key] = $data;
            }

            // TODO: Don't check ttl after first iteration
            // $options['ttl'] = 0;
        }

        return $result;
    }

    /**
     * Test if an item exists.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItem.pre(PreEvent)
     * @triggers hasItem.post(PostEvent)
     * @triggers hasItem.exception(ExceptionEvent)
     */
    public function hasItem($key)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::hasItem($key);
    }

    /**
     * Test multiple items.
     *
     * @param  array $keys
     * @return array Array of found keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers hasItems.pre(PreEvent)
     * @triggers hasItems.post(PostEvent)
     * @triggers hasItems.exception(ExceptionEvent)
     */
    public function hasItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::hasItems($keys);
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $file = $this->getFileSpec($normalizedKey) . '.dat';
        if (!file_exists($file)) {
            return false;
        }

        $ttl = $this->getOptions()->getTtl();
        if ($ttl) {
            ErrorHandler::start();
            $mtime = filemtime($file);
            $error = ErrorHandler::stop();
            if (!$mtime) {
                throw new Exception\RuntimeException(
                    "Error getting mtime of file '{$file}'", 0, $error
                );
            }

            if (time() >= ($mtime + $ttl)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get metadata
     *
     * @param string $key
     * @return array|bool Metadata on success, false on failure
     */
    public function getMetadata($key)
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::getMetadata($key);
    }

    /**
     * Get metadatas
     *
     * @param array $keys
     * @param array $options
     * @return array Associative array of keys and metadata
     */
    public function getMetadatas(array $keys, array $options = array())
    {
        $options = $this->getOptions();
        if ($options->getReadable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::getMetadatas($keys);
    }

    /**
     * Get info by key
     *
     * @param string $normalizedKey
     * @return array|bool Metadata on success, false on failure
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        $options  = $this->getOptions();
        $filespec = $this->getFileSpec($normalizedKey);
        $file     = $filespec . '.dat';

        $metadata = array(
            'filespec' => $filespec,
            'mtime'    => filemtime($file)
        );

        if (!$options->getNoCtime()) {
            $metadata['ctime'] = filectime($file);
        }

        if (!$options->getNoAtime()) {
            $metadata['atime'] = fileatime($file);
        }

        return $metadata;
    }

    /**
     * Internal method to get multiple metadata
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and metadata
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadatas(array & $normalizedKeys)
    {
        $options = $this->getOptions();
        $result  = array();

        foreach ($normalizedKeys as $normalizedKey) {
            $filespec = $this->getFileSpec($normalizedKey);
            $file     = $filespec . '.dat';

            $metadata = array(
                'filespec' => $filespec,
                'mtime'    => filemtime($file),
            );

            if (!$options->getNoCtime()) {
                $metadata['ctime'] = filectime($file);
            }

            if (!$options->getNoAtime()) {
                $metadata['atime'] = fileatime($file);
            }

            $result[$normalizedKey] = $metadata;
        }

        return $result;
    }

    /* writing */

    /**
     * Store an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItem.pre(PreEvent)
     * @triggers setItem.post(PostEvent)
     * @triggers setItem.exception(ExceptionEvent)
     */
    public function setItem($key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }
        return parent::setItem($key, $value);
    }

    /**
     * Store multiple items.
     *
     * @param  array $keyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers setItems.pre(PreEvent)
     * @triggers setItems.post(PostEvent)
     * @triggers setItems.exception(ExceptionEvent)
     */
    public function setItems(array $keyValuePairs)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::setItems($keyValuePairs);
    }

    /**
     * Add an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItem.pre(PreEvent)
     * @triggers addItem.post(PostEvent)
     * @triggers addItem.exception(ExceptionEvent)
     */
    public function addItem($key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::addItem($key, $value);
    }

    /**
     * Add multiple items.
     *
     * @param  array $keyValuePairs
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers addItems.pre(PreEvent)
     * @triggers addItems.post(PostEvent)
     * @triggers addItems.exception(ExceptionEvent)
     */
    public function addItems(array $keyValuePairs)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::addItems($keyValuePairs);
    }

    /**
     * Replace an existing item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItem.pre(PreEvent)
     * @triggers replaceItem.post(PostEvent)
     * @triggers replaceItem.exception(ExceptionEvent)
     */
    public function replaceItem($key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::replaceItem($key, $value);
    }

    /**
     * Replace multiple existing items.
     *
     * @param  array $keyValuePairs
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers replaceItems.pre(PreEvent)
     * @triggers replaceItems.post(PostEvent)
     * @triggers replaceItems.exception(ExceptionEvent)
     */
    public function replaceItems(array $keyValuePairs)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::replaceItems($keyValuePairs);
    }

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $filespec = $this->getFileSpec($normalizedKey);
        $this->prepareDirectoryStructure($filespec);

        // write data in non-blocking mode
        $wouldblock = null;
        $this->putFileContent($filespec . '.dat', $value, true, $wouldblock);

        // delete related tag file (if present)
        $this->unlink($filespec . '.tag');

        // Retry writing data in blocking mode if it was blocked before
        if ($wouldblock) {
            $this->putFileContent($filespec . '.dat', $value);
        }

        return true;
    }

    /**
     * Internal method to store multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        $oldUmask    = null;

        // create an associated array of files and contents to write
        $contents = array();
        foreach ($normalizedKeyValuePairs as $key => & $value) {
            $filespec = $this->getFileSpec($key);
            $this->prepareDirectoryStructure($filespec);

            // *.dat file
            $contents[$filespec . '.dat'] = & $value;

            // *.tag file
            $this->unlink($filespec . '.tag');
        }

        // write to disk
        while ($contents) {
            $nonBlocking = count($contents) > 1;
            $wouldblock  = null;

            foreach ($contents as $file => & $content) {
                $this->putFileContent($file, $content, $nonBlocking, $wouldblock);
                if (!$nonBlocking || !$wouldblock) {
                    unset($contents[$file]);
                }
            }
        }

        // return OK
        return array();
    }

    /**
     * Set an item only if token matches
     *
     * It uses the token received from getItem() to check if the item has
     * changed before overwriting it.
     *
     * @param  mixed  $token
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    public function checkAndSetItem($token, $key, $value)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::checkAndSetItem($token, $key, $value);
    }

    /**
     * Internal method to set an item only if token matches
     *
     * @param  mixed  $token
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     * @see    getItem()
     * @see    setItem()
     */
    protected function internalCheckAndSetItem(& $token, & $normalizedKey, & $value)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        // use filemtime + filesize as CAS token
        $file  = $this->getFileSpec($normalizedKey) . '.dat';
        $check = filemtime($file) . filesize($file);
        if ($token !== $check) {
            return false;
        }

        return $this->internalSetItem($normalizedKey, $value);
    }

    /**
     * Reset lifetime of an item
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItem.pre(PreEvent)
     * @triggers touchItem.post(PostEvent)
     * @triggers touchItem.exception(ExceptionEvent)
     */
    public function touchItem($key)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::touchItem($key);
    }

    /**
     * Reset lifetime of multiple items.
     *
     * @param  array $keys
     * @return array Array of not updated keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers touchItems.pre(PreEvent)
     * @triggers touchItems.post(PostEvent)
     * @triggers touchItems.exception(ExceptionEvent)
     */
    public function touchItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::touchItems($keys);
    }

    /**
     * Internal method to reset lifetime of an item
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalTouchItem(& $normalizedKey)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return false;
        }

        $filespec = $this->getFileSpec($normalizedKey);

        ErrorHandler::start();
        $touch = touch($filespec . '.dat');
        $error = ErrorHandler::stop();
        if (!$touch) {
            throw new Exception\RuntimeException(
                "Error touching file '{$filespec}.dat'", 0, $error
            );
        }

        return true;
    }

    /**
     * Remove an item.
     *
     * @param  string $key
     * @return bool
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItem.pre(PreEvent)
     * @triggers removeItem.post(PostEvent)
     * @triggers removeItem.exception(ExceptionEvent)
     */
    public function removeItem($key)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::removeItem($key);
    }

    /**
     * Remove multiple items.
     *
     * @param  array $keys
     * @return array Array of not removed keys
     * @throws Exception\ExceptionInterface
     *
     * @triggers removeItems.pre(PreEvent)
     * @triggers removeItems.post(PostEvent)
     * @triggers removeItems.exception(ExceptionEvent)
     */
    public function removeItems(array $keys)
    {
        $options = $this->getOptions();
        if ($options->getWritable() && $options->getClearStatCache()) {
            clearstatcache();
        }

        return parent::removeItems($keys);
    }

    /**
     * Internal method to remove an item.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $filespec = $this->getFileSpec($normalizedKey);
        if (!file_exists($filespec . '.dat')) {
            return false;
        } else {
            $this->unlink($filespec . '.dat');
            $this->unlink($filespec . '.tag');
        }
        return true;
    }

    /* status */

    /**
     * Internal method to get capabilities of this adapter
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $marker  = new stdClass();
            $options = $this->getOptions();

            // detect metadata
            $metadata = array('mtime', 'filespec');
            if (!$options->getNoAtime()) {
                $metadata[] = 'atime';
            }
            if (!$options->getNoCtime()) {
                $metadata[] = 'ctime';
            }

            $capabilities = new Capabilities(
                $this,
                $marker,
                array(
                    'supportedDatatypes' => array(
                        'NULL'     => 'string',
                        'boolean'  => 'string',
                        'integer'  => 'string',
                        'double'   => 'string',
                        'string'   => true,
                        'array'    => false,
                        'object'   => false,
                        'resource' => false,
                    ),
                    'supportedMetadata'  => $metadata,
                    'minTtl'             => 1,
                    'maxTtl'             => 0,
                    'staticTtl'          => false,
                    'ttlPrecision'       => 1,
                    'expiredRead'        => true,
                    'maxKeyLength'       => 251, // 255 - strlen(.dat | .tag)
                    'namespaceIsPrefix'  => true,
                    'namespaceSeparator' => $options->getNamespaceSeparator(),
                )
            );

            // update capabilities on change options
            $this->getEventManager()->attach('option', function ($event) use ($capabilities, $marker) {
                $params = $event->getParams();

                if (isset($params['namespace_separator'])) {
                    $capabilities->setNamespaceSeparator($marker, $params['namespace_separator']);
                }

                if (isset($params['no_atime']) || isset($params['no_ctime'])) {
                    $metadata = $capabilities->getSupportedMetadata();

                    if (isset($params['no_atime']) && !$params['no_atime']) {
                        $metadata[] = 'atime';
                    } elseif (isset($params['no_atime']) && ($index = array_search('atime', $metadata)) !== false) {
                        unset($metadata[$index]);
                    }

                    if (isset($params['no_ctime']) && !$params['no_ctime']) {
                        $metadata[] = 'ctime';
                    } elseif (isset($params['no_ctime']) && ($index = array_search('ctime', $metadata)) !== false) {
                        unset($metadata[$index]);
                    }

                    $capabilities->setSupportedMetadata($marker, $metadata);
                }
            });

            $this->capabilityMarker = $marker;
            $this->capabilities     = $capabilities;
        }

        return $this->capabilities;
    }

    /* internal */

    /**
     * Removes directories recursive by namespace
     *
     * @param  string $dir    Directory to delete
     * @param  string $prefix Namespace + Separator
     * @return bool
     */
    protected function rmDir($dir, $prefix)
    {
        $glob = glob(
            $dir . DIRECTORY_SEPARATOR . $prefix  . '*',
            GLOB_ONLYDIR | GLOB_NOESCAPE | GLOB_NOSORT
        );
        if (!$glob) {
            // On some systems glob returns false even on empty result
            return true;
        }

        $ret = true;
        foreach ($glob as $subdir) {
            // skip removing current directory if removing of sub-directory failed
            if ($this->rmDir($subdir, $prefix)) {
                // ignore not empty directories
                ErrorHandler::start();
                $ret = rmdir($subdir) && $ret;
                ErrorHandler::stop();
            } else {
                $ret = false;
            }
        }

        return $ret;
    }

    /**
     * Get file spec of the given key and namespace
     *
     * @param  string $normalizedKey
     * @return string
     */
    protected function getFileSpec($normalizedKey)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $path      = $options->getCacheDir() . DIRECTORY_SEPARATOR;
        $level     = $options->getDirLevel();

        $fileSpecId = $path . $prefix . $normalizedKey . '/' . $level;
        if ($this->lastFileSpecId !== $fileSpecId) {
            if ($level > 0) {
                // create up to 256 directories per directory level
                $hash = md5($normalizedKey);
                for ($i = 0, $max = ($level * 2); $i < $max; $i+= 2) {
                    $path .= $prefix . $hash[$i] . $hash[$i+1] . DIRECTORY_SEPARATOR;
                }
            }

            $this->lastFileSpecId = $fileSpecId;
            $this->lastFileSpec   = $path . $prefix . $normalizedKey;
        }

        return $this->lastFileSpec;
    }

    /**
     * Read info file
     *
     * @param  string  $file
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return array|bool The info array or false if file wasn't found
     * @throws Exception\RuntimeException
     */
    protected function readInfoFile($file, $nonBlocking = false, & $wouldblock = null)
    {
        if (!file_exists($file)) {
            return false;
        }

        $content = $this->getFileContent($file, $nonBlocking, $wouldblock);
        if ($nonBlocking && $wouldblock) {
            return false;
        }

        ErrorHandler::start();
        $ifo = unserialize($content);
        $err = ErrorHandler::stop();
        if (!is_array($ifo)) {
            throw new Exception\RuntimeException(
                "Corrupted info file '{$file}'", 0, $err
            );
        }

        return $ifo;
    }

    /**
     * Read a complete file
     *
     * @param  string  $file        File complete path
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function getFileContent($file, $nonBlocking = false, & $wouldblock = null)
    {
        $locking    = $this->getOptions()->getFileLocking();
        $wouldblock = null;

        ErrorHandler::start();

        // if file locking enabled -> file_get_contents can't be used
        if ($locking) {
            $fp = fopen($file, 'rb');
            if ($fp === false) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "Error opening file '{$file}'", 0, $err
                );
            }

            if ($nonBlocking) {
                $lock = flock($fp, LOCK_SH | LOCK_NB, $wouldblock);
                if ($wouldblock) {
                    fclose($fp);
                    ErrorHandler::stop();
                    return;
                }
            } else {
                $lock = flock($fp, LOCK_SH);
            }

            if (!$lock) {
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "Error locking file '{$file}'", 0, $err
                );
            }

            $res = stream_get_contents($fp);
            if ($res === false) {
                flock($fp, LOCK_UN);
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    'Error getting stream contents', 0, $err
                );
            }

            flock($fp, LOCK_UN);
            fclose($fp);

        // if file locking disabled -> file_get_contents can be used
        } else {
            $res = file_get_contents($file, false);
            if ($res === false) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "Error getting file contents for file '{$file}'", 0, $err
                );
            }
        }

        ErrorHandler::stop();
        return $res;
    }

    /**
     * Prepares a directory structure for the given file(spec)
     * using the configured directory level.
     *
     * @param string $file
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function prepareDirectoryStructure($file)
    {
        $options = $this->getOptions();
        $level   = $options->getDirLevel();

        // Directory structure is required only if directory level > 0
        if (!$level) {
            return;
        }

        // Directory structure already exists
        $pathname = dirname($file);
        if (file_exists($pathname)) {
            return;
        }

        $perm     = $options->getDirPermission();
        $umask    = $options->getUmask();
        if ($umask !== false && $perm !== false) {
            $perm = $perm & ~$umask;
        }

        ErrorHandler::start();

        if ($perm === false || $level == 1) {
            // build-in mkdir function is enough

            $umask = ($umask !== false) ? umask($umask) : false;
            $res   = mkdir($pathname, ($perm !== false) ? $perm : 0777, true);

            if ($umask !== false) {
                umask($umask);
            }

            if (!$res) {
                $oct = ($perm === false) ? '777' : decoct($perm);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "mkdir('{$pathname}', 0{$oct}, true) failed", 0, $err
                );
            }

            if ($perm !== false && !chmod($pathname, $perm)) {
                $oct = decoct($perm);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "chmod('{$pathname}', 0{$oct}) failed", 0, $err
                );
            }

        } else {
            // build-in mkdir function sets permission together with current umask
            // which doesn't work well on multo threaded webservers
            // -> create directories one by one and set permissions

            // find existing path and missing path parts
            $parts = array();
            $path  = $pathname;
            while (!file_exists($path)) {
                array_unshift($parts, basename($path));
                $nextPath = dirname($path);
                if ($nextPath === $path) {
                    break;
                }
                $path = $nextPath;
            }

            // make all missing path parts
            foreach ($parts as $part) {
                $path.= DIRECTORY_SEPARATOR . $part;

                // create a single directory, set and reset umask immediately
                $umask = ($umask !== false) ? umask($umask) : false;
                $res   = mkdir($path, ($perm === false) ? 0777 : $perm, false);
                if ($umask !== false) {
                    umask($umask);
                }

                if (!$res) {
                    $oct = ($perm === false) ? '777' : decoct($perm);
                    $err = ErrorHandler::stop();
                    throw new Exception\RuntimeException(
                        "mkdir('{$path}', 0{$oct}, false) failed"
                    );
                }

                if ($perm !== false && !chmod($path, $perm)) {
                    $oct = decoct($perm);
                    $err = ErrorHandler::stop();
                    throw new Exception\RuntimeException(
                        "chmod('{$path}', 0{$oct}) failed"
                    );
                }
            }
        }

        ErrorHandler::stop();
    }

    /**
     * Write content to a file
     *
     * @param  string  $file        File complete path
     * @param  string  $data        Data to write
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function putFileContent($file, $data, $nonBlocking = false, & $wouldblock = null)
    {
        $options     = $this->getOptions();
        $locking     = $options->getFileLocking();
        $nonBlocking = $locking && $nonBlocking;
        $wouldblock  = null;

        $umask = $options->getUmask();
        $perm  = $options->getFilePermission();
        if ($umask !== false && $perm !== false) {
            $perm = $perm & ~$umask;
        }

        ErrorHandler::start();

        // if locking and non blocking is enabled -> file_put_contents can't used
        if ($locking && $nonBlocking) {

            $umask = ($umask !== false) ? umask($umask) : false;

            $fp = fopen($file, 'cb');

            if ($umask) {
                umask($umask);
            }

            if (!$fp) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "Error opening file '{$file}'", 0, $err
                );
            }

            if ($perm !== false && !chmod($file, $perm)) {
                fclose($fp);
                $oct = decoct($perm);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("chmod('{$file}', 0{$oct}) failed", 0, $err);
            }

            if (!flock($fp, LOCK_EX | LOCK_NB, $wouldblock)) {
                fclose($fp);
                $err = ErrorHandler::stop();
                if ($wouldblock) {
                    return;
                } else {
                    throw new Exception\RuntimeException("Error locking file '{$file}'", 0, $err);
                }
            }

            if (fwrite($fp, $data) === false) {
                flock($fp, LOCK_UN);
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error writing file '{$file}'", 0, $err);
            }

            if (!ftruncate($fp, strlen($data))) {
                flock($fp, LOCK_UN);
                fclose($fp);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("Error truncating file '{$file}'", 0, $err);
            }

            flock($fp, LOCK_UN);
            fclose($fp);

        // else -> file_put_contents can be used
        } else {
            $flags = 0;
            if ($locking) {
                $flags = $flags | LOCK_EX;
            }

            $umask = ($umask !== false) ? umask($umask) : false;

            $rs = file_put_contents($file, $data, $flags);

            if ($umask) {
                umask($umask);
            }

            if ($rs === false) {
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "Error writing file '{$file}'", 0, $err
                );
            }

            if ($perm !== false && !chmod($file, $perm)) {
                $oct = decoct($perm);
                $err = ErrorHandler::stop();
                throw new Exception\RuntimeException("chmod('{$file}', 0{$oct}) failed", 0, $err);
            }
        }

        ErrorHandler::stop();
    }

    /**
     * Unlink a file
     *
     * @param string $file
     * @return void
     * @throws RuntimeException
     */
    protected function unlink($file)
    {
        ErrorHandler::start();
        $res = unlink($file);
        $err = ErrorHandler::stop();

        // only throw exception if file still exists after deleting
        if (!$res && file_exists($file)) {
            throw new Exception\RuntimeException(
                "Error unlinking file '{$file}'; file still exists", 0, $err
            );
        }
    }
}
