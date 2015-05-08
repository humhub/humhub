<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation;

use Countable;
use RecursiveIterator;
use RecursiveIteratorIterator;
use Traversable;
use Zend\Stdlib\ErrorHandler;

/**
 * Zend\Navigation\Container
 *
 * AbstractContainer class for Zend\Navigation\Page classes.
 */
abstract class AbstractContainer implements Countable, RecursiveIterator
{
    /**
     * Contains sub pages
     *
     * @var array
     */
    protected $pages = array();

    /**
     * An index that contains the order in which to iterate pages
     *
     * @var array
     */
    protected $index = array();

    /**
     * Whether index is dirty and needs to be re-arranged
     *
     * @var bool
     */
    protected $dirtyIndex = false;

    // Internal methods:

    /**
     * Sorts the page index according to page order
     *
     * @return void
     */
    protected function sort()
    {
        if (!$this->dirtyIndex) {
            return;
        }

        $newIndex = array();
        $index    = 0;

        foreach ($this->pages as $hash => $page) {
            $order = $page->getOrder();
            if ($order === null) {
                $newIndex[$hash] = $index;
                $index++;
            } else {
                $newIndex[$hash] = $order;
            }
        }

        asort($newIndex);
        $this->index      = $newIndex;
        $this->dirtyIndex = false;
    }

    // Public methods:

    /**
     * Notifies container that the order of pages are updated
     *
     * @return void
     */
    public function notifyOrderUpdated()
    {
        $this->dirtyIndex = true;
    }

    /**
     * Adds a page to the container
     *
     * This method will inject the container as the given page's parent by
     * calling {@link Page\AbstractPage::setParent()}.
     *
     * @param  Page\AbstractPage|array|Traversable $page  page to add
     * @return self fluent interface, returns self
     * @throws Exception\InvalidArgumentException if page is invalid
     */
    public function addPage($page)
    {
        if ($page === $this) {
            throw new Exception\InvalidArgumentException(
                'A page cannot have itself as a parent'
            );
        }

        if (!$page instanceof Page\AbstractPage) {
            if (!is_array($page) && !$page instanceof Traversable) {
                throw new Exception\InvalidArgumentException(
                    'Invalid argument: $page must be an instance of '
                    . 'Zend\Navigation\Page\AbstractPage or Traversable, or an array'
                );
            }
            $page = Page\AbstractPage::factory($page);
        }

        $hash = $page->hashCode();

        if (array_key_exists($hash, $this->index)) {
            // page is already in container
            return $this;
        }

        // adds page to container and sets dirty flag
        $this->pages[$hash] = $page;
        $this->index[$hash] = $page->getOrder();
        $this->dirtyIndex = true;

        // inject self as page parent
        $page->setParent($this);

        return $this;
    }

    /**
     * Adds several pages at once
     *
     * @param  array|Traversable|AbstractContainer $pages pages to add
     * @return self fluent interface, returns self
     * @throws Exception\InvalidArgumentException if $pages is not array,
     *                                            Traversable or AbstractContainer
     */
    public function addPages($pages)
    {
        if (!is_array($pages) && !$pages instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $pages must be an array, an '
                . 'instance of Traversable or an instance of '
                . 'Zend\Navigation\AbstractContainer'
            );
        }

        // Because adding a page to a container removes it from the original
        // (see {@link Page\AbstractPage::setParent()}), iteration of the
        // original container will break. As such, we need to iterate the
        // container into an array first.
        if ($pages instanceof AbstractContainer) {
            $pages = iterator_to_array($pages);
        }

        foreach ($pages as $page) {
            if (null === $page) {
                continue;
            }
            $this->addPage($page);
        }

        return $this;
    }

    /**
     * Sets pages this container should have, removing existing pages
     *
     * @param  array $pages pages to set
     * @return self fluent interface, returns self
     */
    public function setPages(array $pages)
    {
        $this->removePages();
        return $this->addPages($pages);
    }

    /**
     * Returns pages in the container
     *
     * @return array  array of Page\AbstractPage instances
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Removes the given page from the container
     *
     * @param  Page\AbstractPage|int $page page to remove, either a page
     *                                     instance or a specific page order
     * @return bool whether the removal was successful
     */
    public function removePage($page)
    {
        if ($page instanceof Page\AbstractPage) {
            $hash = $page->hashCode();
        } elseif (is_int($page)) {
            $this->sort();
            if (!$hash = array_search($page, $this->index)) {
                return false;
            }
        } else {
            return false;
        }

        if (isset($this->pages[$hash])) {
            unset($this->pages[$hash]);
            unset($this->index[$hash]);
            $this->dirtyIndex = true;
            return true;
        }

        return false;
    }

    /**
     * Removes all pages in container
     *
     * @return self fluent interface, returns self
     */
    public function removePages()
    {
        $this->pages = array();
        $this->index = array();
        return $this;
    }

    /**
     * Checks if the container has the given page
     *
     * @param  Page\AbstractPage $page page to look for
     * @param  bool $recursive [optional] whether to search recursively.
     *                         Default is false.
     * @return bool whether page is in container
     */
    public function hasPage(Page\AbstractPage $page, $recursive = false)
    {
        if (array_key_exists($page->hashCode(), $this->index)) {
            return true;
        } elseif ($recursive) {
            foreach ($this->pages as $childPage) {
                if ($childPage->hasPage($page, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns true if container contains any pages
     *
     * @return bool  whether container has any pages
     */
    public function hasPages()
    {
        return count($this->index) > 0;
    }

    /**
     * Returns a child page matching $property == $value, or null if not found
     *
     * @param  string $property        name of property to match against
     * @param  mixed  $value           value to match property against
     * @return Page\AbstractPage|null  matching page or null
     */
    public function findOneBy($property, $value)
    {
        $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $page) {
            if ($page->get($property) == $value) {
                return $page;
            }
        }

        return null;
    }

    /**
     * Returns all child pages matching $property == $value, or an empty array
     * if no pages are found
     *
     * @param  string $property  name of property to match against
     * @param  mixed  $value     value to match property against
     * @return array  array containing only Page\AbstractPage instances
     */
    public function findAllBy($property, $value)
    {
        $found = array();

        $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $page) {
            if ($page->get($property) == $value) {
                $found[] = $page;
            }
        }

        return $found;
    }

    /**
     * Returns page(s) matching $property == $value
     *
     * @param  string $property  name of property to match against
     * @param  mixed  $value     value to match property against
     * @param  bool   $all       [optional] whether an array of all matching
     *                           pages should be returned, or only the first.
     *                           If true, an array will be returned, even if not
     *                           matching pages are found. If false, null will
     *                           be returned if no matching page is found.
     *                           Default is false.
     * @return Page\AbstractPage|null  matching page or null
     */
    public function findBy($property, $value, $all = false)
    {
        if ($all) {
            return $this->findAllBy($property, $value);
        }

        return $this->findOneBy($property, $value);
    }

    /**
     * Magic overload: Proxy calls to finder methods
     *
     * Examples of finder calls:
     * <code>
     * // METHOD                    // SAME AS
     * $nav->findByLabel('foo');    // $nav->findOneBy('label', 'foo');
     * $nav->findOneByLabel('foo'); // $nav->findOneBy('label', 'foo');
     * $nav->findAllByClass('foo'); // $nav->findAllBy('class', 'foo');
     * </code>
     *
     * @param  string $method             method name
     * @param  array  $arguments          method arguments
     * @throws Exception\BadMethodCallException  if method does not exist
     */
    public function __call($method, $arguments)
    {
        ErrorHandler::start(E_WARNING);
        $result = preg_match('/(find(?:One|All)?By)(.+)/', $method, $match);
        $error  = ErrorHandler::stop();
        if (!$result) {
            throw new Exception\BadMethodCallException(sprintf(
                'Bad method call: Unknown method %s::%s',
                get_class($this),
                $method
            ), 0, $error);
        }
        return $this->{$match[1]}($match[2], $arguments[0]);

    }

    /**
     * Returns an array representation of all pages in container
     *
     * @return array
     */
    public function toArray()
    {
        $this->sort();
        $pages   = array();
        $indexes = array_keys($this->index);
        foreach ($indexes as $hash) {
            $pages[] = $this->pages[$hash]->toArray();
        }
        return $pages;
    }

    // RecursiveIterator interface:

    /**
     * Returns current page
     *
     * Implements RecursiveIterator interface.
     *
     * @return Page\AbstractPage current page or null
     * @throws Exception\OutOfBoundsException  if the index is invalid
     */
    public function current()
    {
        $this->sort();

        current($this->index);
        $hash = key($this->index);
        if (!isset($this->pages[$hash])) {
            throw new Exception\OutOfBoundsException(
                'Corruption detected in container; '
                . 'invalid key found in internal iterator'
            );
        }

        return $this->pages[$hash];
    }

    /**
     * Returns hash code of current page
     *
     * Implements RecursiveIterator interface.
     *
     * @return string  hash code of current page
     */
    public function key()
    {
        $this->sort();
        return key($this->index);
    }

    /**
     * Moves index pointer to next page in the container
     *
     * Implements RecursiveIterator interface.
     *
     * @return void
     */
    public function next()
    {
        $this->sort();
        next($this->index);
    }

    /**
     * Sets index pointer to first page in the container
     *
     * Implements RecursiveIterator interface.
     *
     * @return void
     */
    public function rewind()
    {
        $this->sort();
        reset($this->index);
    }

    /**
     * Checks if container index is valid
     *
     * Implements RecursiveIterator interface.
     *
     * @return bool
     */
    public function valid()
    {
        $this->sort();
        return current($this->index) !== false;
    }

    /**
     * Proxy to hasPages()
     *
     * Implements RecursiveIterator interface.
     *
     * @return bool  whether container has any pages
     */
    public function hasChildren()
    {
        return $this->hasPages();
    }

    /**
     * Returns the child container.
     *
     * Implements RecursiveIterator interface.
     *
     * @return Page\AbstractPage|null
     */
    public function getChildren()
    {
        $hash = key($this->index);

        if (isset($this->pages[$hash])) {
            return $this->pages[$hash];
        }

        return null;
    }

    // Countable interface:

    /**
     * Returns number of pages in container
     *
     * Implements Countable interface.
     *
     * @return int  number of pages in the container
     */
    public function count()
    {
        return count($this->index);
    }
}
