<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Storage\Directory;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Storage
 */
interface DirectoryInterface
{

    /**
     * Closes the store.
     *
     * @return void
     */
    public function close();

    /**
     * Returns an array of strings, one for each file in the directory.
     *
     * @return array
     */
    public function fileList();

    /**
     * Creates a new, empty file in the directory with the given $filename.
     *
     * @param string $filename
     * @return \ZendSearch\Lucene\Storage\File\FileInterface
     */
    public function createFile($filename);


    /**
     * Removes an existing $filename in the directory.
     *
     * @param string $filename
     * @return void
     */
    public function deleteFile($filename);

    /**
     * Purge file if it's cached by directory object
     *
     * Method is used to prevent 'too many open files' error
     *
     * @param string $filename
     * @return void
     */
    public function purgeFile($filename);

    /**
     * Returns true if a file with the given $filename exists.
     *
     * @param string $filename
     * @return boolean
     */
    public function fileExists($filename);


    /**
     * Returns the length of a $filename in the directory.
     *
     * @param string $filename
     * @return integer
     */
    public function fileLength($filename);


    /**
     * Returns the UNIX timestamp $filename was last modified.
     *
     * @param string $filename
     * @return integer
     */
    public function fileModified($filename);


    /**
     * Renames an existing file in the directory.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public function renameFile($from, $to);


    /**
     * Sets the modified time of $filename to now.
     *
     * @param string $filename
     * @return void
     */
    public function touchFile($filename);


    /**
     * Returns a Zend_Search_Lucene_Storage_File object for a given $filename in the directory.
     *
     * If $shareHandler option is true, then file handler can be shared between File Object
     * requests. It speed-ups performance, but makes problems with file position.
     * Shared handler are good for short atomic requests.
     * Non-shared handlers are useful for stream file reading (especial for compound files).
     *
     * @param string $filename
     * @param boolean $shareHandler
     * @return \ZendSearch\Lucene\Storage\File\FileInterface
     */
    public function getFileObject($filename, $shareHandler = true);
}
