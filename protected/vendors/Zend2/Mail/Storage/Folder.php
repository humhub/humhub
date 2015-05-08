<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage;

use RecursiveIterator;

class Folder implements RecursiveIterator
{
    /**
     * subfolders of folder array(localName => \Zend\Mail\Storage\Folder folder)
     * @var array
     */
    protected $folders;

    /**
     * local name (name of folder in parent folder)
     * @var string
     */
    protected $localName;

    /**
     * global name (absolute name of folder)
     * @var string
     */
    protected $globalName;

    /**
     * folder is selectable if folder is able to hold messages, else it's just a parent folder
     * @var bool
     */
    protected $selectable = true;

    /**
     * create a new mail folder instance
     *
     * @param string $localName  name of folder in current subdirectory
     * @param string $globalName absolute name of folder
     * @param bool   $selectable if true folder holds messages, if false it's just a parent for subfolders (Default: true)
     * @param array  $folders    init with given instances of \Zend\Mail\Storage\Folder as subfolders
     */
    public function __construct($localName, $globalName = '', $selectable = true, array $folders = array())
    {
        $this->localName  = $localName;
        $this->globalName = $globalName ? $globalName : $localName;
        $this->selectable = $selectable;
        $this->folders    = $folders;
    }

    /**
     * implements RecursiveIterator::hasChildren()
     *
     * @return bool current element has children
     */
    public function hasChildren()
    {
        $current = $this->current();
        return $current && $current instanceof Folder && !$current->isLeaf();
    }

    /**
     * implements RecursiveIterator::getChildren()
     *
     * @return \Zend\Mail\Storage\Folder same as self::current()
     */
    public function getChildren()
    {
        return $this->current();
    }

    /**
     * implements Iterator::valid()
     *
     * @return bool check if there's a current element
     */
    public function valid()
    {
        return key($this->folders) !== null;
    }

    /**
     * implements Iterator::next()
     */
    public function next()
    {
        next($this->folders);
    }

    /**
     * implements Iterator::key()
     *
     * @return string key/local name of current element
     */
    public function key()
    {
        return key($this->folders);
    }

    /**
     * implements Iterator::current()
     *
     * @return \Zend\Mail\Storage\Folder current folder
     */
    public function current()
    {
        return current($this->folders);
    }

    /**
     * implements Iterator::rewind()
     */
    public function rewind()
    {
        reset($this->folders);
    }

    /**
     * get subfolder named $name
     *
     * @param  string $name wanted subfolder
     * @throws Exception\InvalidArgumentException
     * @return \Zend\Mail\Storage\Folder folder named $folder
     */
    public function __get($name)
    {
        if (!isset($this->folders[$name])) {
            throw new Exception\InvalidArgumentException("no subfolder named $name");
        }

        return $this->folders[$name];
    }

    /**
     * add or replace subfolder named $name
     *
     * @param string $name local name of subfolder
     * @param \Zend\Mail\Storage\Folder $folder instance for new subfolder
     */
    public function __set($name, Folder $folder)
    {
        $this->folders[$name] = $folder;
    }

    /**
     * remove subfolder named $name
     *
     * @param string $name local name of subfolder
     */
    public function __unset($name)
    {
        unset($this->folders[$name]);
    }

    /**
     * magic method for easy output of global name
     *
     * @return string global name of folder
     */
    public function __toString()
    {
        return (string) $this->getGlobalName();
    }

    /**
     * get local name
     *
     * @return string local name
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * get global name
     *
     * @return string global name
     */
    public function getGlobalName()
    {
        return $this->globalName;
    }

    /**
     * is this folder selectable?
     *
     * @return bool selectable
     */
    public function isSelectable()
    {
        return $this->selectable;
    }

    /**
     * check if folder has no subfolder
     *
     * @return bool true if no subfolders
     */
    public function isLeaf()
    {
        return empty($this->folders);
    }
}
