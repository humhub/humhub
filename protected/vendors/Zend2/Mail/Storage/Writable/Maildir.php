<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage\Writable;

use RecursiveIteratorIterator;
use Zend\Mail\Exception as MailException;
use Zend\Mail\Storage;
use Zend\Mail\Storage\Exception as StorageException;
use Zend\Mail\Storage\Folder;
use Zend\Stdlib\ErrorHandler;

class Maildir extends Folder\Maildir implements WritableInterface
{
    // TODO: init maildir (+ constructor option create if not found)

    /**
     * use quota and size of quota if given
     *
     * @var bool|int
     */
    protected $quota;

    /**
     * create a new maildir
     *
     * If the given dir is already a valid maildir this will not fail.
     *
     * @param string $dir directory for the new maildir (may already exist)
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
     */
    public static function initMaildir($dir)
    {
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new StorageException\InvalidArgumentException('maildir must be a directory if already exists');
            }
        } else {
            ErrorHandler::start();
            $test  = mkdir($dir);
            $error = ErrorHandler::stop();
            if (!$test) {
                $dir = dirname($dir);
                if (!file_exists($dir)) {
                    throw new StorageException\InvalidArgumentException("parent $dir not found", 0, $error);
                } elseif (!is_dir($dir)) {
                    throw new StorageException\InvalidArgumentException("parent $dir not a directory", 0, $error);
                } else {
                    throw new StorageException\RuntimeException('cannot create maildir', 0, $error);
                }
            }
        }

        foreach (array('cur', 'tmp', 'new') as $subdir) {
            ErrorHandler::start();
            $test  = mkdir($dir . DIRECTORY_SEPARATOR . $subdir);
            $error = ErrorHandler::stop();
            if (!$test) {
                // ignore if dir exists (i.e. was already valid maildir or two processes try to create one)
                if (!file_exists($dir . DIRECTORY_SEPARATOR . $subdir)) {
                    throw new StorageException\RuntimeException('could not create subdir ' . $subdir, 0, $error);
                }
            }
        }
    }

    /**
     * Create instance with parameters
     * Additional parameters are (see parent for more):
     *   - create if true a new maildir is create if none exists
     *
     * @param  $params array mail reader specific parameters
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object) $params;
        }

        if (!empty($params->create) && isset($params->dirname) && !file_exists($params->dirname . DIRECTORY_SEPARATOR . 'cur')) {
            self::initMaildir($params->dirname);
        }

        parent::__construct($params);
    }

    /**
     * create a new folder
     *
     * This method also creates parent folders if necessary. Some mail storages may restrict, which folder
     * may be used as parent or which chars may be used in the folder name
     *
     * @param   string                           $name         global name of folder, local name if $parentFolder is set
     * @param   string|\Zend\Mail\Storage\Folder $parentFolder parent folder for new folder, else root folder is parent
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     * @return  string only used internally (new created maildir)
     */
    public function createFolder($name, $parentFolder = null)
    {
        if ($parentFolder instanceof Folder) {
            $folder = $parentFolder->getGlobalName() . $this->delim . $name;
        } elseif ($parentFolder != null) {
            $folder = rtrim($parentFolder, $this->delim) . $this->delim . $name;
        } else {
            $folder = $name;
        }

        $folder = trim($folder, $this->delim);

        // first we check if we try to create a folder that does exist
        $exists = null;
        try {
            $exists = $this->getFolders($folder);
        } catch (MailException\ExceptionInterface $e) {
            // ok
        }
        if ($exists) {
            throw new StorageException\RuntimeException('folder already exists');
        }

        if (strpos($folder, $this->delim . $this->delim) !== false) {
            throw new StorageException\RuntimeException('invalid name - folder parts may not be empty');
        }

        if (strpos($folder, 'INBOX' . $this->delim) === 0) {
            $folder = substr($folder, 6);
        }

        $fulldir = $this->rootdir . '.' . $folder;

        // check if we got tricked and would create a dir outside of the rootdir or not as direct child
        if (strpos($folder, DIRECTORY_SEPARATOR) !== false || strpos($folder, '/') !== false
            || dirname($fulldir) . DIRECTORY_SEPARATOR != $this->rootdir
        ) {
            throw new StorageException\RuntimeException('invalid name - no directory separator allowed in folder name');
        }

        // has a parent folder?
        $parent = null;
        if (strpos($folder, $this->delim)) {
            // let's see if the parent folder exists
            $parent = substr($folder, 0, strrpos($folder, $this->delim));
            try {
                $this->getFolders($parent);
            } catch (MailException\ExceptionInterface $e) {
                // does not - create parent folder
                $this->createFolder($parent);
            }
        }

        ErrorHandler::start();
        if (!mkdir($fulldir) || !mkdir($fulldir . DIRECTORY_SEPARATOR . 'cur')) {
            $error = ErrorHandler::stop();
            throw new StorageException\RuntimeException('error while creating new folder, may be created incompletely', 0, $error);
        }
        ErrorHandler::stop();

        mkdir($fulldir . DIRECTORY_SEPARATOR . 'new');
        mkdir($fulldir . DIRECTORY_SEPARATOR . 'tmp');

        $localName                             = $parent ? substr($folder, strlen($parent) + 1) : $folder;
        $this->getFolders($parent)->$localName = new Folder($localName, $folder, true);

        return $fulldir;
    }

    /**
     * remove a folder
     *
     * @param  string|Folder $name      name or instance of folder
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function removeFolder($name)
    {
        // TODO: This could fail in the middle of the task, which is not optimal.
        // But there is no defined standard way to mark a folder as removed and there is no atomar fs-op
        // to remove a directory. Also moving the folder to a/the trash folder is not possible, as
        // all parent folders must be created. What we could do is add a dash to the front of the
        // directory name and it should be ignored as long as other processes obey the standard.

        if ($name instanceof Folder) {
            $name = $name->getGlobalName();
        }

        $name = trim($name, $this->delim);
        if (strpos($name, 'INBOX' . $this->delim) === 0) {
            $name = substr($name, 6);
        }

        // check if folder exists and has no children
        if (!$this->getFolders($name)->isLeaf()) {
            throw new StorageException\RuntimeException('delete children first');
        }

        if ($name == 'INBOX' || $name == DIRECTORY_SEPARATOR || $name == '/') {
            throw new StorageException\RuntimeException('wont delete INBOX');
        }

        if ($name == $this->getCurrentFolder()) {
            throw new StorageException\RuntimeException('wont delete selected folder');
        }

        foreach (array('tmp', 'new', 'cur', '.') as $subdir) {
            $dir = $this->rootdir . '.' . $name . DIRECTORY_SEPARATOR . $subdir;
            if (!file_exists($dir)) {
                continue;
            }
            $dh = opendir($dir);
            if (!$dh) {
                throw new StorageException\RuntimeException("error opening $subdir");
            }
            while (($entry = readdir($dh)) !== false) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if (!unlink($dir . DIRECTORY_SEPARATOR . $entry)) {
                    throw new StorageException\RuntimeException("error cleaning $subdir");
                }
            }
            closedir($dh);
            if ($subdir !== '.') {
                if (!rmdir($dir)) {
                    throw new StorageException\RuntimeException("error removing $subdir");
                }
            }
        }

        if (!rmdir($this->rootdir . '.' . $name)) {
            // at least we should try to make it a valid maildir again
            mkdir($this->rootdir . '.' . $name . DIRECTORY_SEPARATOR . 'cur');
            throw new StorageException\RuntimeException("error removing maindir");
        }

        $parent    = strpos($name, $this->delim) ? substr($name, 0, strrpos($name, $this->delim)) : null;
        $localName = $parent ? substr($name, strlen($parent) + 1) : $name;
        unset($this->getFolders($parent)->$localName);
    }

    /**
     * rename and/or move folder
     *
     * The new name has the same restrictions as in createFolder()
     *
     * @param  string|\Zend\Mail\Storage\Folder $oldName name or instance of folder
     * @param  string                           $newName new global name of folder
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function renameFolder($oldName, $newName)
    {
        // TODO: This is also not atomar and has similar problems as removeFolder()

        if ($oldName instanceof Folder) {
            $oldName = $oldName->getGlobalName();
        }

        $oldName = trim($oldName, $this->delim);
        if (strpos($oldName, 'INBOX' . $this->delim) === 0) {
            $oldName = substr($oldName, 6);
        }

        $newName = trim($newName, $this->delim);
        if (strpos($newName, 'INBOX' . $this->delim) === 0) {
            $newName = substr($newName, 6);
        }

        if (strpos($newName, $oldName . $this->delim) === 0) {
            throw new StorageException\RuntimeException('new folder cannot be a child of old folder');
        }

        // check if folder exists and has no children
        $folder = $this->getFolders($oldName);

        if ($oldName == 'INBOX' || $oldName == DIRECTORY_SEPARATOR || $oldName == '/') {
            throw new StorageException\RuntimeException('wont rename INBOX');
        }

        if ($oldName == $this->getCurrentFolder()) {
            throw new StorageException\RuntimeException('wont rename selected folder');
        }

        $newdir = $this->createFolder($newName);

        if (!$folder->isLeaf()) {
            foreach ($folder as $k => $v) {
                $this->renameFolder($v->getGlobalName(), $newName . $this->delim . $k);
            }
        }

        $olddir = $this->rootdir . '.' . $folder;
        foreach (array('tmp', 'new', 'cur') as $subdir) {
            $subdir = DIRECTORY_SEPARATOR . $subdir;
            if (!file_exists($olddir . $subdir)) {
                continue;
            }
            // using copy or moving files would be even better - but also much slower
            if (!rename($olddir . $subdir, $newdir . $subdir)) {
                throw new StorageException\RuntimeException('error while moving ' . $subdir);
            }
        }
        // create a dummy if removing fails - otherwise we can't read it next time
        mkdir($olddir . DIRECTORY_SEPARATOR . 'cur');
        $this->removeFolder($oldName);
    }

    /**
     * create a uniqueid for maildir filename
     *
     * This is nearly the format defined in the maildir standard. The microtime() call should already
     * create a uniqueid, the pid is for multicore/-cpu machine that manage to call this function at the
     * exact same time, and uname() gives us the hostname for multiple machines accessing the same storage.
     *
     * If someone disables posix we create a random number of the same size, so this method should also
     * work on Windows - if you manage to get maildir working on Windows.
     * Microtime could also be disabled, although I've never seen it.
     *
     * @return string new uniqueid
     */
    protected function _createUniqueId()
    {
        $id = '';
        $id .= microtime(true);
        $id .= '.' . getmypid();
        $id .= '.' . php_uname('n');

        return $id;
    }

    /**
     * open a temporary maildir file
     *
     * makes sure tmp/ exists and create a file with a unique name
     * you should close the returned filehandle!
     *
     * @param   string $folder name of current folder without leading .
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     * @return  array array('dirname' => dir of maildir folder, 'uniq' => unique id, 'filename' => name of create file
     *                     'handle'  => file opened for writing)
     */
    protected function _createTmpFile($folder = 'INBOX')
    {
        if ($folder == 'INBOX') {
            $tmpdir = $this->rootdir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        } else {
            $tmpdir = $this->rootdir . '.' . $folder . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        }
        if (!file_exists($tmpdir)) {
            if (!mkdir($tmpdir)) {
                throw new StorageException\RuntimeException('problems creating tmp dir');
            }
        }

        // we should retry to create a unique id if a file with the same name exists
        // to avoid a script timeout we only wait 1 second (instead of 2) and stop
        // after a defined retry count
        // if you change this variable take into account that it can take up to $maxTries seconds
        // normally we should have a valid unique name after the first try, we're just following the "standard" here
        $maxTries = 5;
        for ($i = 0; $i < $maxTries; ++$i) {
            $uniq = $this->_createUniqueId();
            if (!file_exists($tmpdir . $uniq)) {
                // here is the race condition! - as defined in the standard
                // to avoid having a long time between stat()ing the file and creating it we're opening it here
                // to mark the filename as taken
                $fh = fopen($tmpdir . $uniq, 'w');
                if (!$fh) {
                    throw new StorageException\RuntimeException('could not open temp file');
                }
                break;
            }
            sleep(1);
        }

        if (!$fh) {
            throw new StorageException\RuntimeException("tried $maxTries unique ids for a temp file, but all were taken"
                . ' - giving up');
        }

        return array('dirname'  => $this->rootdir . '.' . $folder,
                     'uniq'     => $uniq,
                     'filename' => $tmpdir . $uniq,
                     'handle'   => $fh);
    }

    /**
     * create an info string for filenames with given flags
     *
     * @param   array $flags wanted flags, with the reference you'll get the set flags with correct key (= char for flag)
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
     * @return  string info string for version 2 filenames including the leading colon
     */
    protected function _getInfoString(&$flags)
    {
        // accessing keys is easier, faster and it removes duplicated flags
        $wantedFlags = array_flip($flags);
        if (isset($wantedFlags[Storage::FLAG_RECENT])) {
            throw new StorageException\InvalidArgumentException('recent flag may not be set');
        }

        $info  = ':2,';
        $flags = array();
        foreach (Storage\Maildir::$knownFlags as $char => $flag) {
            if (!isset($wantedFlags[$flag])) {
                continue;
            }
            $info .= $char;
            $flags[$char] = $flag;
            unset($wantedFlags[$flag]);
        }

        if (!empty($wantedFlags)) {
            $wantedFlags = implode(', ', array_keys($wantedFlags));
            throw new StorageException\InvalidArgumentException('unknown flag(s): ' . $wantedFlags);
        }

        return $info;
    }

    /**
     * append a new message to mail storage
     *
     * @param   string|stream                              $message message as string or stream resource
     * @param   null|string|\Zend\Mail\Storage\Folder      $folder  folder for new message, else current folder is taken
     * @param   null|array                                 $flags   set flags for new message, else a default set is used
     * @param   bool                                       $recent  handle this mail as if recent flag has been set,
     *                                                              should only be used in delivery
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    // not yet * @param string|\Zend\Mail\Message|\Zend\Mime\Message $message message as string or instance of message class
    public function appendMessage($message, $folder = null, $flags = null, $recent = false)
    {
        if ($this->quota && $this->checkQuota()) {
            throw new StorageException\RuntimeException('storage is over quota!');
        }

        if ($folder === null) {
            $folder = $this->currentFolder;
        }

        if (!($folder instanceof Folder)) {
            $folder = $this->getFolders($folder);
        }

        if ($flags === null) {
            $flags = array(Storage::FLAG_SEEN);
        }
        $info     = $this->_getInfoString($flags);
        $tempFile = $this->_createTmpFile($folder->getGlobalName());

        // TODO: handle class instances for $message
        if (is_resource($message) && get_resource_type($message) == 'stream') {
            stream_copy_to_stream($message, $tempFile['handle']);
        } else {
            fwrite($tempFile['handle'], $message);
        }
        fclose($tempFile['handle']);

        // we're adding the size to the filename for maildir++
        $size = filesize($tempFile['filename']);
        if ($size !== false) {
            $info = ',S=' . $size . $info;
        }
        $newFilename = $tempFile['dirname'] . DIRECTORY_SEPARATOR;
        $newFilename .= $recent ? 'new' : 'cur';
        $newFilename .= DIRECTORY_SEPARATOR . $tempFile['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!link($tempFile['filename'], $newFilename)) {
            $exception = new StorageException\RuntimeException('cannot link message file to final dir');
        }

        ErrorHandler::start(E_WARNING);
        unlink($tempFile['filename']);
        ErrorHandler::stop();

        if ($exception) {
            throw $exception;
        }

        $this->files[] = array('uniq'     => $tempFile['uniq'],
                                'flags'    => $flags,
                                'filename' => $newFilename);
        if ($this->quota) {
            $this->_addQuotaEntry((int) $size, 1);
        }
    }

    /**
     * copy an existing message
     *
     * @param  int                              $id     number of message
     * @param  string|\Zend\Mail\Storage\Folder $folder name or instance of targer folder
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function copyMessage($id, $folder)
    {
        if ($this->quota && $this->checkQuota()) {
            throw new StorageException\RuntimeException('storage is over quota!');
        }

        if (!($folder instanceof Folder)) {
            $folder = $this->getFolders($folder);
        }

        $filedata = $this->_getFileData($id);
        $oldFile  = $filedata['filename'];
        $flags    = $filedata['flags'];

        // copied message can't be recent
        while (($key = array_search(Storage::FLAG_RECENT, $flags)) !== false) {
            unset($flags[$key]);
        }
        $info = $this->_getInfoString($flags);

        // we're creating the copy as temp file before moving to cur/
        $tempFile = $this->_createTmpFile($folder->getGlobalName());
        // we don't write directly to the file
        fclose($tempFile['handle']);

        // we're adding the size to the filename for maildir++
        $size = filesize($oldFile);
        if ($size !== false) {
            $info = ',S=' . $size . $info;
        }

        $newFile = $tempFile['dirname'] . DIRECTORY_SEPARATOR . 'cur' . DIRECTORY_SEPARATOR . $tempFile['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!copy($oldFile, $tempFile['filename'])) {
            $exception = new StorageException\RuntimeException('cannot copy message file');
        } elseif (!link($tempFile['filename'], $newFile)) {
            $exception = new StorageException\RuntimeException('cannot link message file to final dir');
        }

        ErrorHandler::start(E_WARNING);
        unlink($tempFile['filename']);
        ErrorHandler::stop();

        if ($exception) {
            throw $exception;
        }

        if ($folder->getGlobalName() == $this->currentFolder
            || ($this->currentFolder == 'INBOX' && $folder->getGlobalName() == '/')
        ) {
            $this->files[] = array('uniq'     => $tempFile['uniq'],
                                    'flags'    => $flags,
                                    'filename' => $newFile);
        }

        if ($this->quota) {
            $this->_addQuotaEntry((int) $size, 1);
        }
    }

    /**
     * move an existing message
     *
     * @param  int                              $id     number of message
     * @param  string|\Zend\Mail\Storage\Folder $folder name or instance of targer folder
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function moveMessage($id, $folder)
    {
        if (!($folder instanceof Folder)) {
            $folder = $this->getFolders($folder);
        }

        if ($folder->getGlobalName() == $this->currentFolder
            || ($this->currentFolder == 'INBOX' && $folder->getGlobalName() == '/')
        ) {
            throw new StorageException\RuntimeException('target is current folder');
        }

        $filedata = $this->_getFileData($id);
        $oldFile  = $filedata['filename'];
        $flags    = $filedata['flags'];

        // moved message can't be recent
        while (($key = array_search(Storage::FLAG_RECENT, $flags)) !== false) {
            unset($flags[$key]);
        }
        $info = $this->_getInfoString($flags);

        // reserving a new name
        $tempFile = $this->_createTmpFile($folder->getGlobalName());
        fclose($tempFile['handle']);

        // we're adding the size to the filename for maildir++
        $size = filesize($oldFile);
        if ($size !== false) {
            $info = ',S=' . $size . $info;
        }

        $newFile = $tempFile['dirname'] . DIRECTORY_SEPARATOR . 'cur' . DIRECTORY_SEPARATOR . $tempFile['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!rename($oldFile, $newFile)) {
            $exception = new StorageException\RuntimeException('cannot move message file');
        }

        ErrorHandler::start(E_WARNING);
        unlink($tempFile['filename']);
        ErrorHandler::stop();

        if ($exception) {
            throw $exception;
        }

        unset($this->files[$id - 1]);
        // remove the gap
        $this->files = array_values($this->files);
    }


    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param   int   $id    number of message
     * @param   array $flags new flags for message
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function setFlags($id, $flags)
    {
        $info     = $this->_getInfoString($flags);
        $filedata = $this->_getFileData($id);

        // NOTE: double dirname to make sure we always move to cur. if recent flag has been set (message is in new) it will be moved to cur.
        $newFilename = dirname(dirname($filedata['filename'])) . DIRECTORY_SEPARATOR . 'cur' . DIRECTORY_SEPARATOR . "$filedata[uniq]$info";

        ErrorHandler::start();
        $test  = rename($filedata['filename'], $newFilename);
        $error = ErrorHandler::stop();
        if (!$test) {
            throw new StorageException\RuntimeException('cannot rename file', 0, $error);
        }

        $filedata['flags']    = $flags;
        $filedata['filename'] = $newFilename;

        $this->files[$id - 1] = $filedata;
    }


    /**
     * stub for not supported message deletion
     *
     * @param $id
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function removeMessage($id)
    {
        $filename = $this->_getFileData($id, 'filename');

        if ($this->quota) {
            $size = filesize($filename);
        }

        ErrorHandler::start();
        $test  = unlink($filename);
        $error = ErrorHandler::stop();
        if (!$test) {
            throw new StorageException\RuntimeException('cannot remove message', 0, $error);
        }
        unset($this->files[$id - 1]);
        // remove the gap
        $this->files = array_values($this->files);
        if ($this->quota) {
            $this->_addQuotaEntry(0 - (int) $size, -1);
        }
    }

    /**
     * enable/disable quota and set a quota value if wanted or needed
     *
     * You can enable/disable quota with true/false. If you don't have
     * a MDA or want to enforce a quota value you can also set this value
     * here. Use array('size' => SIZE_QUOTA, 'count' => MAX_MESSAGE) do
     * define your quota. Order of these fields does matter!
     *
     * @param bool|array $value new quota value
     */
    public function setQuota($value)
    {
        $this->quota = $value;
    }

    /**
     * get currently set quota
     *
     * @see \Zend\Mail\Storage\Writable\Maildir::setQuota()
     * @param bool $fromStorage
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     * @return bool|array
     */
    public function getQuota($fromStorage = false)
    {
        if ($fromStorage) {
            ErrorHandler::start(E_WARNING);
            $fh    = fopen($this->rootdir . 'maildirsize', 'r');
            $error = ErrorHandler::stop();
            if (!$fh) {
                throw new StorageException\RuntimeException('cannot open maildirsize', 0, $error);
            }
            $definition = fgets($fh);
            fclose($fh);
            $definition = explode(',', trim($definition));
            $quota      = array();
            foreach ($definition as $member) {
                $key = $member[strlen($member) - 1];
                if ($key == 'S' || $key == 'C') {
                    $key = $key == 'C' ? 'count' : 'size';
                }
                $quota[$key] = substr($member, 0, -1);
            }
            return $quota;
        }

        return $this->quota;
    }

    /**
     * @see http://www.inter7.com/courierimap/README.maildirquota.html "Calculating maildirsize"
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     * @return array
     */
    protected function _calculateMaildirsize()
    {
        $timestamps = array();
        $messages   = 0;
        $totalSize  = 0;

        if (is_array($this->quota)) {
            $quota = $this->quota;
        } else {
            try {
                $quota = $this->getQuota(true);
            } catch (StorageException\ExceptionInterface $e) {
                throw new StorageException\RuntimeException('no quota definition found', 0, $e);
            }
        }

        $folders = new RecursiveIteratorIterator($this->getFolders(), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($folders as $folder) {
            $subdir = $folder->getGlobalName();
            if ($subdir == 'INBOX') {
                $subdir = '';
            } else {
                $subdir = '.' . $subdir;
            }
            if ($subdir == 'Trash') {
                continue;
            }

            foreach (array('cur', 'new') as $subsubdir) {
                $dirname = $this->rootdir . $subdir . DIRECTORY_SEPARATOR . $subsubdir . DIRECTORY_SEPARATOR;
                if (!file_exists($dirname)) {
                    continue;
                }
                // NOTE: we are using mtime instead of "the latest timestamp". The latest would be atime
                // and as we are accessing the directory it would make the whole calculation useless.
                $timestamps[$dirname] = filemtime($dirname);

                $dh = opendir($dirname);
                // NOTE: Should have been checked in constructor. Not throwing an exception here, quotas will
                // therefore not be fully enforced, but next request will fail anyway, if problem persists.
                if (!$dh) {
                    continue;
                }


                while (($entry = readdir()) !== false) {
                    if ($entry[0] == '.' || !is_file($dirname . $entry)) {
                        continue;
                    }

                    if (strpos($entry, ',S=')) {
                        strtok($entry, '=');
                        $filesize = strtok(':');
                        if (is_numeric($filesize)) {
                            $totalSize += $filesize;
                            ++$messages;
                            continue;
                        }
                    }
                    $size = filesize($dirname . $entry);
                    if ($size === false) {
                        // ignore, as we assume file got removed
                        continue;
                    }
                    $totalSize += $size;
                    ++$messages;
                }
            }
        }

        $tmp        = $this->_createTmpFile();
        $fh         = $tmp['handle'];
        $definition = array();
        foreach ($quota as $type => $value) {
            if ($type == 'size' || $type == 'count') {
                $type = $type == 'count' ? 'C' : 'S';
            }
            $definition[] = $value . $type;
        }
        $definition = implode(',', $definition);
        fwrite($fh, "$definition\n");
        fwrite($fh, "$totalSize $messages\n");
        fclose($fh);
        rename($tmp['filename'], $this->rootdir . 'maildirsize');
        foreach ($timestamps as $dir => $timestamp) {
            if ($timestamp < filemtime($dir)) {
                unlink($this->rootdir . 'maildirsize');
                break;
            }
        }

        return array('size'  => $totalSize,
                     'count' => $messages,
                     'quota' => $quota);
    }

    /**
     * @see http://www.inter7.com/courierimap/README.maildirquota.html "Calculating the quota for a Maildir++"
     * @param bool $forceRecalc
     * @return array
     */
    protected function _calculateQuota($forceRecalc = false)
    {
        $fh          = null;
        $totalSize   = 0;
        $messages    = 0;
        $maildirsize = '';
        if (!$forceRecalc && file_exists($this->rootdir . 'maildirsize') && filesize($this->rootdir . 'maildirsize') < 5120) {
            $fh = fopen($this->rootdir . 'maildirsize', 'r');
        }
        if ($fh) {
            $maildirsize = fread($fh, 5120);
            if (strlen($maildirsize) >= 5120) {
                fclose($fh);
                $fh          = null;
                $maildirsize = '';
            }
        }
        if (!$fh) {
            $result     = $this->_calculateMaildirsize();
            $totalSize = $result['size'];
            $messages   = $result['count'];
            $quota      = $result['quota'];
        } else {
            $maildirsize = explode("\n", $maildirsize);
            if (is_array($this->quota)) {
                $quota = $this->quota;
            } else {
                $definition = explode(',', $maildirsize[0]);
                $quota      = array();
                foreach ($definition as $member) {
                    $key = $member[strlen($member) - 1];
                    if ($key == 'S' || $key == 'C') {
                        $key = $key == 'C' ? 'count' : 'size';
                    }
                    $quota[$key] = substr($member, 0, -1);
                }
            }
            unset($maildirsize[0]);
            foreach ($maildirsize as $line) {
                list($size, $count) = explode(' ', trim($line));
                $totalSize += $size;
                $messages += $count;
            }
        }

        $overQuota = false;
        $overQuota = $overQuota || (isset($quota['size']) && $totalSize > $quota['size']);
        $overQuota = $overQuota || (isset($quota['count']) && $messages > $quota['count']);
        // NOTE: $maildirsize equals false if it wasn't set (AKA we recalculated) or it's only
        // one line, because $maildirsize[0] gets unsetted.
        // Also we're using local time to calculate the 15 minute offset. Touching a file just for known the
        // local time of the file storage isn't worth the hassle.
        if ($overQuota && ($maildirsize || filemtime($this->rootdir . 'maildirsize') > time() - 900)) {
            $result     = $this->_calculateMaildirsize();
            $totalSize  = $result['size'];
            $messages   = $result['count'];
            $quota      = $result['quota'];
            $overQuota = false;
            $overQuota = $overQuota || (isset($quota['size']) && $totalSize > $quota['size']);
            $overQuota = $overQuota || (isset($quota['count']) && $messages > $quota['count']);
        }

        if ($fh) {
            // TODO is there a safe way to keep the handle open for writing?
            fclose($fh);
        }

        return array('size'       => $totalSize,
                     'count'      => $messages,
                     'quota'      => $quota,
                     'over_quota' => $overQuota);
    }

    protected function _addQuotaEntry($size, $count = 1)
    {
        if (!file_exists($this->rootdir . 'maildirsize')) {
            // TODO: should get file handler from _calculateQuota
        }
        $size  = (int) $size;
        $count = (int) $count;
        file_put_contents($this->rootdir . 'maildirsize', "$size $count\n", FILE_APPEND);
    }

    /**
     * check if storage is currently over quota
     *
     * @see _calculateQuota()
     * @param bool $detailedResponse return known data of quota and current size and message count
     * @param bool $forceRecalc
     * @return bool|array over quota state or detailed response
     */
    public function checkQuota($detailedResponse = false, $forceRecalc = false)
    {
        $result = $this->_calculateQuota($forceRecalc);
        return $detailedResponse ? $result : $result['over_quota'];
    }
}
