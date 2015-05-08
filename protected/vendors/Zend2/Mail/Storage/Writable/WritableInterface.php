<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage\Writable;

interface WritableInterface
{
    /**
     * create a new folder
     *
     * This method also creates parent folders if necessary. Some mail storages may restrict, which folder
     * may be used as parent or which chars may be used in the folder name
     *
     * @param string                           $name         global name of folder, local name if $parentFolder is set
     * @param string|\Zend\Mail\Storage\Folder $parentFolder parent folder for new folder, else root folder is parent
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function createFolder($name, $parentFolder = null);

    /**
     * remove a folder
     *
     * @param string|\Zend\Mail\Storage\Folder $name name or instance of folder
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function removeFolder($name);

    /**
     * rename and/or move folder
     *
     * The new name has the same restrictions as in createFolder()
     *
     * @param string|\Zend\Mail\Storage\Folder $oldName name or instance of folder
     * @param string                           $newName new global name of folder
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function renameFolder($oldName, $newName);

    /**
     * append a new message to mail storage
     *
     * @param  string|\Zend\Mail\Message|\Zend\Mime\Message $message message as string or instance of message class
     * @param  null|string|\Zend\Mail\Storage\Folder        $folder  folder for new message, else current folder is taken
     * @param  null|array                                   $flags   set flags for new message, else a default set is used
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function appendMessage($message, $folder = null, $flags = null);

    /**
     * copy an existing message
     *
     * @param  int                              $id     number of message
     * @param  string|\Zend\Mail\Storage\Folder $folder name or instance of target folder
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function copyMessage($id, $folder);

    /**
     * move an existing message
     *
     * @param  int                              $id     number of message
     * @param  string|\Zend\Mail\Storage\Folder $folder name or instance of target folder
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function moveMessage($id, $folder);

    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param  int   $id    number of message
     * @param  array $flags new flags for message
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function setFlags($id, $flags);
}
