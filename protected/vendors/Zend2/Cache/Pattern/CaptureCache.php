<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Pattern;

use Zend\Cache\Exception;
use Zend\Stdlib\ErrorHandler;

class CaptureCache extends AbstractPattern
{
    /**
     * Start the cache
     *
     * @param  string $pageId  Page identifier
     * @return void
     */
    public function start($pageId = null)
    {
        if ($pageId === null) {
            $pageId = $this->detectPageId();
        }

        $that = $this;
        ob_start(function ($content) use ($that, $pageId) {
            $that->set($content, $pageId);

            // http://php.net/manual/function.ob-start.php
            // -> If output_callback  returns FALSE original input is sent to the browser.
            return false;
        });

        ob_implicit_flush(false);
    }

    /**
     * Write content to page identity
     *
     * @param string      $content
     * @param null|string $pageId
     * @throws Exception\LogicException
     */
    public function set($content, $pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if ($publicDir === null) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }

        if ($pageId === null) {
            $pageId = $this->detectPageId();
        }

        $path = $this->pageId2Path($pageId);
        $file = $path . DIRECTORY_SEPARATOR . $this->pageId2Filename($pageId);

        $this->createDirectoryStructure($publicDir . DIRECTORY_SEPARATOR . $path);
        $this->putFileContent($publicDir . DIRECTORY_SEPARATOR . $file, $content);
    }

    /**
     * Get from cache
     *
     * @param  null|string $pageId
     * @return bool|string
     * @throws Exception\LogicException
     * @throws Exception\RuntimeException
     */
    public function get($pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if ($publicDir === null) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }

        if ($pageId === null) {
            $pageId = $this->detectPageId();
        }

        $file = $publicDir
            . DIRECTORY_SEPARATOR . $this->pageId2Path($pageId)
            . DIRECTORY_SEPARATOR . $this->pageId2Filename($pageId);

        if (file_exists($file)) {
            ErrorHandler::start();
            $content = file_get_contents($file);
            $error   = ErrorHandler::stop();
            if ($content === false) {
                throw new Exception\RuntimeException(
                    "Failed to read cached pageId '{$pageId}'", 0, $error
                );
            }
            return $content;
        }
    }

    /**
     * Checks if a cache with given id exists
     *
     * @param  null|string $pageId
     * @throws Exception\LogicException
     * @return bool
     */
    public function has($pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if ($publicDir === null) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }

        if ($pageId === null) {
            $pageId = $this->detectPageId();
        }

        $file = $publicDir
            . DIRECTORY_SEPARATOR . $this->pageId2Path($pageId)
            . DIRECTORY_SEPARATOR . $this->pageId2Filename($pageId);

        return file_exists($file);
    }

    /**
     * Remove from cache
     *
     * @param  null|string $pageId
     * @throws Exception\LogicException
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function remove($pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if ($publicDir === null) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }

        if ($pageId === null) {
            $pageId = $this->detectPageId();
        }

        $file = $publicDir
            . DIRECTORY_SEPARATOR . $this->pageId2Path($pageId)
            . DIRECTORY_SEPARATOR . $this->pageId2Filename($pageId);

        if (file_exists($file)) {
            ErrorHandler::start();
            $res = unlink($file);
            $err = ErrorHandler::stop();
            if (!$res) {
                throw new Exception\RuntimeException(
                    "Failed to remove cached pageId '{$pageId}'", 0, $err
                );
            }
            return true;
        }

        return false;
    }

    /**
     * Clear cached pages matching glob pattern
     *
     * @param string $pattern
     * @throws Exception\LogicException
     */
    public function clearByGlob($pattern = '**')
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if ($publicDir === null) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }

        $it = new \GlobIterator(
            $publicDir . '/' . $pattern,
            \GlobIterator::CURRENT_AS_SELF | \GlobIterator::SKIP_DOTS | \GlobIterator::UNIX_PATHS
        );
        foreach ($it as $pathname => $entry) {
            if ($entry->isFile()) {
                unlink($pathname);
            }
        }
    }

    /**
     * Determine the page to save from the request
     *
     * @throws Exception\RuntimeException
     * @return string
     */
    protected function detectPageId()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            throw new Exception\RuntimeException("Can't auto-detect current page identity");
        }

        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Get filename for page id
     *
     * @param string $pageId
     * @return string
     */
    protected function pageId2Filename($pageId)
    {
        if (substr($pageId, -1) === '/') {
            return $this->getOptions()->getIndexFilename();
        }

        return basename($pageId);
    }

    /**
     * Get path for page id
     *
     * @param string $pageId
     * @return string
     */
    protected function pageId2Path($pageId)
    {
        if (substr($pageId, -1) == '/') {
            $path = rtrim($pageId, '/');
        } else {
            $path = dirname($pageId);
        }

        // convert requested "/" to the valid local directory separator
        if ('/' != DIRECTORY_SEPARATOR) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        return $path;
    }

    /**
     * Write content to a file
     *
     * @param  string  $file File complete path
     * @param  string  $data Data to write
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function putFileContent($file, $data)
    {
        $options = $this->getOptions();
        $locking = $options->getFileLocking();
        $perm    = $options->getFilePermission();
        $umask   = $options->getUmask();
        if ($umask !== false && $perm !== false) {
            $perm = $perm & ~$umask;
        }

        ErrorHandler::start();

        $umask = ($umask !== false) ? umask($umask) : false;
        $rs    = file_put_contents($file, $data, $locking ? LOCK_EX : 0);
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

        ErrorHandler::stop();
    }

    /**
     * Creates directory if not already done.
     *
     * @param string $pathname
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function createDirectoryStructure($pathname)
    {
        // Directory structure already exists
        if (file_exists($pathname)) {
            return;
        }

        $options = $this->getOptions();
        $perm    = $options->getDirPermission();
        $umask   = $options->getUmask();
        if ($umask !== false && $perm !== false) {
            $perm = $perm & ~$umask;
        }

        ErrorHandler::start();

        if ($perm === false) {
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
     * Returns the generated file name.
     *
     * @param null|string $pageId
     * @return string
     */
    public function getFilename($pageId = null)
    {
        if ($pageId === null) {
            $pageId = $this->detectPageId();
        }

        $publicDir = $this->getOptions()->getPublicDir();
        $path      = $this->pageId2Path($pageId);
        $file      = $path . DIRECTORY_SEPARATOR . $this->pageId2Filename($pageId);

        return $publicDir . $file;
    }
}
