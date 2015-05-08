<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage\Folder;

use Zend\Mail\Storage;
use Zend\Mail\Storage\Exception;
use Zend\Stdlib\ErrorHandler;

class Mbox extends Storage\Mbox implements FolderInterface
{
    /**
     * \Zend\Mail\Storage\Folder root folder for folder structure
     * @var \Zend\Mail\Storage\Folder
     */
    protected $rootFolder;

    /**
     * rootdir of folder structure
     * @var string
     */
    protected $rootdir;

    /**
     * name of current folder
     * @var string
     */
    protected $currentFolder;

    /**
     * Create instance with parameters
     *
     * Disallowed parameters are:
     *   - filename use \Zend\Mail\Storage\Mbox for a single file
     * Supported parameters are:
     *   - dirname rootdir of mbox structure
     *   - folder intial selected folder, default is 'INBOX'
     *
     * @param  $params array mail reader specific parameters
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object) $params;
        }

        if (isset($params->filename)) {
            throw new Exception\InvalidArgumentException('use \Zend\Mail\Storage\Mbox for a single file');
        }

        if (!isset($params->dirname) || !is_dir($params->dirname)) {
            throw new Exception\InvalidArgumentException('no valid dirname given in params');
        }

        $this->rootdir = rtrim($params->dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $this->_buildFolderTree($this->rootdir);
        $this->selectFolder(!empty($params->folder) ? $params->folder : 'INBOX');
        $this->has['top']      = true;
        $this->has['uniqueid'] = false;
    }

    /**
     * find all subfolders and mbox files for folder structure
     *
     * Result is save in \Zend\Mail\Storage\Folder instances with the root in $this->rootFolder.
     * $parentFolder and $parentGlobalName are only used internally for recursion.
     *
     * @param string $currentDir call with root dir, also used for recursion.
     * @param \Zend\Mail\Storage\Folder|null $parentFolder used for recursion
     * @param string $parentGlobalName used for recursion
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
     */
    protected function _buildFolderTree($currentDir, $parentFolder = null, $parentGlobalName = '')
    {
        if (!$parentFolder) {
            $this->rootFolder = new Storage\Folder('/', '/', false);
            $parentFolder = $this->rootFolder;
        }

        ErrorHandler::start(E_WARNING);
        $dh = opendir($currentDir);
        ErrorHandler::stop();
        if (!$dh) {
            throw new Exception\InvalidArgumentException("can't read dir $currentDir");
        }
        while (($entry = readdir($dh)) !== false) {
            // ignore hidden files for mbox
            if ($entry[0] == '.') {
                continue;
            }
            $absoluteEntry = $currentDir . $entry;
            $globalName = $parentGlobalName . DIRECTORY_SEPARATOR . $entry;
            if (is_file($absoluteEntry) && $this->isMboxFile($absoluteEntry)) {
                $parentFolder->$entry = new Storage\Folder($entry, $globalName);
                continue;
            }
            if (!is_dir($absoluteEntry) /* || $entry == '.' || $entry == '..' */) {
                continue;
            }
            $folder = new Storage\Folder($entry, $globalName, false);
            $parentFolder->$entry = $folder;
            $this->_buildFolderTree($absoluteEntry . DIRECTORY_SEPARATOR, $folder, $globalName);
        }

        closedir($dh);
    }

    /**
     * get root folder or given folder
     *
     * @param string $rootFolder get folder structure for given folder, else root
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
     * @return \Zend\Mail\Storage\Folder root or wanted folder
     */
    public function getFolders($rootFolder = null)
    {
        if (!$rootFolder) {
            return $this->rootFolder;
        }

        $currentFolder = $this->rootFolder;
        $subname = trim($rootFolder, DIRECTORY_SEPARATOR);
        while ($currentFolder) {
            ErrorHandler::start(E_NOTICE);
            list($entry, $subname) = explode(DIRECTORY_SEPARATOR, $subname, 2);
            ErrorHandler::stop();
            $currentFolder = $currentFolder->$entry;
            if (!$subname) {
                break;
            }
        }

        if ($currentFolder->getGlobalName() != DIRECTORY_SEPARATOR . trim($rootFolder, DIRECTORY_SEPARATOR)) {
            throw new Exception\InvalidArgumentException("folder $rootFolder not found");
        }
        return $currentFolder;
    }

    /**
     * select given folder
     *
     * folder must be selectable!
     *
     * @param \Zend\Mail\Storage\Folder|string $globalName global name of folder or instance for subfolder
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function selectFolder($globalName)
    {
        $this->currentFolder = (string) $globalName;

        // getting folder from folder tree for validation
        $folder = $this->getFolders($this->currentFolder);

        try {
            $this->openMboxFile($this->rootdir . $folder->getGlobalName());
        } catch (Exception\ExceptionInterface $e) {
            // check what went wrong
            if (!$folder->isSelectable()) {
                throw new Exception\RuntimeException("{$this->currentFolder} is not selectable", 0, $e);
            }
            // seems like file has vanished; rebuilding folder tree - but it's still an exception
            $this->_buildFolderTree($this->rootdir);
            throw new Exception\RuntimeException('seems like the mbox file has vanished, I\'ve rebuild the ' .
                                                         'folder tree, search for an other folder and try again', 0, $e);
        }
    }

    /**
     * get \Zend\Mail\Storage\Folder instance for current folder
     *
     * @return \Zend\Mail\Storage\Folder instance of current folder
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getCurrentFolder()
    {
        return $this->currentFolder;
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
        return array_merge(parent::__sleep(), array('currentFolder', 'rootFolder', 'rootdir'));
    }

    /**
     * magic method for unserialize(), with this method you can cache the mbox class
     */
    public function __wakeup()
    {
        // if cache is stall selectFolder() rebuilds the tree on error
        parent::__wakeup();
    }
}
