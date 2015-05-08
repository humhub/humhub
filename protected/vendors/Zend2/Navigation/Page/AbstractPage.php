<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation\Page;

use Traversable;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Exception;
use Zend\Permissions\Acl\Resource\ResourceInterface as AclResource;
use Zend\Stdlib\ArrayUtils;

/**
 * Base class for Zend\Navigation\Page pages
 */
abstract class AbstractPage extends AbstractContainer
{
    /**
     * Page label
     *
     * @var string|null
     */
    protected $label;

    /**
     * Fragment identifier (anchor identifier)
     *
     * The fragment identifier (anchor identifier) pointing to an anchor within
     * a resource that is subordinate to another, primary resource.
     * The fragment identifier introduced by a hash mark "#".
     * Example: http://www.example.org/foo.html#bar ("bar" is the fragment identifier)
     *
     * @link http://www.w3.org/TR/html401/intro/intro.html#fragment-uri
     *
     * @var string|null
     */
    protected $fragment;

    /**
     * Page id
     *
     * @var string|null
     */
    protected $id;

    /**
     * Style class for this page (CSS)
     *
     * @var string|null
     */
    protected $class;

    /**
     * A more descriptive title for this page
     *
     * @var string|null
     */
    protected $title;

    /**
     * This page's target
     *
     * @var string|null
     */
    protected $target;

    /**
     * Forward links to other pages
     *
     * @link http://www.w3.org/TR/html4/struct/links.html#h-12.3.1
     *
     * @var array
     */
    protected $rel = array();

    /**
     * Reverse links to other pages
     *
     * @link http://www.w3.org/TR/html4/struct/links.html#h-12.3.1
     *
     * @var array
     */
    protected $rev = array();

    /**
     * Page order used by parent container
     *
     * @var int|null
     */
    protected $order;

    /**
     * ACL resource associated with this page
     *
     * @var string|AclResource|null
     */
    protected $resource;

    /**
     * ACL privilege associated with this page
     *
     * @var string|null
     */
    protected $privilege;

    /**
     * Permission associated with this page
     *
     * @var mixed|null
     */
    protected $permission;

    /**
     * Whether this page should be considered active
     *
     * @var bool
     */
    protected $active = false;

    /**
     * Whether this page should be considered visible
     *
     * @var bool
     */
    protected $visible = true;

    /**
     * Parent container
     *
     * @var \Zend\Navigation\AbstractContainer|null
     */
    protected $parent;

    /**
     * Custom page properties, used by __set(), __get() and __isset()
     *
     * @var array
     */
    protected $properties = array();

    // Initialization:

    /**
     * Factory for Zend\Navigation\Page classes
     *
     * A specific type to construct can be specified by specifying the key
     * 'type' in $options. If type is 'uri' or 'mvc', the type will be resolved
     * to Zend\Navigation\Page\Uri or Zend\Navigation\Page\Mvc. Any other value
     * for 'type' will be considered the full name of the class to construct.
     * A valid custom page class must extend Zend\Navigation\Page\AbstractPage.
     *
     * If 'type' is not given, the type of page to construct will be determined
     * by the following rules:
     * - If $options contains either of the keys 'action', 'controller',
     *   or 'route', a Zend\Navigation\Page\Mvc page will be created.
     * - If $options contains the key 'uri', a Zend\Navigation\Page\Uri page
     *   will be created.
     *
     * @param  array|Traversable $options  options used for creating page
     * @return AbstractPage  a page instance
     * @throws Exception\InvalidArgumentException if $options is not
     *                                            array/Traversable
     * @throws Exception\InvalidArgumentException if 'type' is specified
     *                                            but class not found
     * @throws Exception\InvalidArgumentException if something goes wrong
     *                                            during instantiation of
     *                                            the page
     * @throws Exception\InvalidArgumentException if 'type' is given, and
     *                                            the specified type does
     *                                            not extend this class
     * @throws Exception\InvalidArgumentException if unable to determine
     *                                            which class to instantiate
     */
    public static function factory($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $options must be an array or Traversable'
            );
        }

        if (isset($options['type'])) {
            $type = $options['type'];
            if (is_string($type) && !empty($type)) {
                switch (strtolower($type)) {
                    case 'mvc':
                        $type = 'Zend\Navigation\Page\Mvc';
                        break;
                    case 'uri':
                        $type = 'Zend\Navigation\Page\Uri';
                        break;
                }

                if (!class_exists($type, true)) {
                    throw new Exception\InvalidArgumentException(
                        'Cannot find class ' . $type
                    );
                }

                $page = new $type($options);
                if (!$page instanceof self) {
                    throw new Exception\InvalidArgumentException(
                        sprintf(
                            'Invalid argument: Detected type "%s", which ' .
                            'is not an instance of Zend\Navigation\Page',
                            $type
                        )
                    );
                }
                return $page;
            }
        }

        $hasUri = isset($options['uri']);
        $hasMvc = isset($options['action']) || isset($options['controller'])
                || isset($options['route']);

        if ($hasMvc) {
            return new Mvc($options);
        } elseif ($hasUri) {
            return new Uri($options);
        } else {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: Unable to determine class to instantiate'
            );
        }
    }

    /**
     * Page constructor
     *
     * @param  array|Traversable $options [optional] page options. Default is
     *                                    null, which should set defaults.
     * @throws Exception\InvalidArgumentException if invalid options are given
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }

        // do custom initialization
        $this->init();
    }

    /**
     * Initializes page (used by subclasses)
     *
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Sets page properties using options from an associative array
     *
     * Each key in the array corresponds to the according set*() method, and
     * each word is separated by underscores, e.g. the option 'target'
     * corresponds to setTarget(), and the option 'reset_params' corresponds to
     * the method setResetParams().
     *
     * @param  array $options associative array of options to set
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException  if invalid options are given
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    // Accessors:

    /**
     * Sets page label
     *
     * @param  string $label new page label
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException if empty/no string is given
     */
    public function setLabel($label)
    {
        if (null !== $label && !is_string($label)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $label must be a string or null'
            );
        }

        $this->label = $label;
        return $this;
    }

    /**
     * Returns page label
     *
     * @return string  page label or null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets a fragment identifier
     *
     * @param  string $fragment new fragment identifier
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException if empty/no string is given
     */
    public function setFragment($fragment)
    {
        if (null !== $fragment && !is_string($fragment)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $fragment must be a string or null'
            );
        }

        $this->fragment = $fragment;
        return $this;
    }

    /**
     * Returns fragment identifier
     *
     * @return string|null  fragment identifier
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Sets page id
     *
     * @param  string|null $id [optional] id to set. Default is null,
     *                         which sets no id.
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException  if not given string or null
     */
    public function setId($id = null)
    {
        if (null !== $id && !is_string($id) && !is_numeric($id)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $id must be a string, number or null'
            );
        }

        $this->id = null === $id ? $id : (string) $id;

        return $this;
    }

    /**
     * Returns page id
     *
     * @return string|null  page id or null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets page CSS class
     *
     * @param  string|null $class [optional] CSS class to set. Default
     *                            is null, which sets no CSS class.
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException  if not given string or null
     */
    public function setClass($class = null)
    {
        if (null !== $class && !is_string($class)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $class must be a string or null'
            );
        }

        $this->class = $class;
        return $this;
    }

    /**
     * Returns page class (CSS)
     *
     * @return string|null  page's CSS class or null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets page title
     *
     * @param  string $title [optional] page title. Default is
     *                       null, which sets no title.
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException if not given string or null
     */
    public function setTitle($title = null)
    {
        if (null !== $title && !is_string($title)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $title must be a non-empty string'
            );
        }

        $this->title = $title;
        return $this;
    }

    /**
     * Returns page title
     *
     * @return string|null  page title or null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets page target
     *
     * @param  string|null $target [optional] target to set. Default is
     *                             null, which sets no target.
     *
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException if target is not string or null
     */
    public function setTarget($target = null)
    {
        if (null !== $target && !is_string($target)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $target must be a string or null'
            );
        }

        $this->target = $target;
        return $this;
    }

    /**
     * Returns page target
     *
     * @return string|null  page target or null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Sets the page's forward links to other pages
     *
     * This method expects an associative array of forward links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  array|Traversable $relations  [optional] an associative array of
     *                           forward links to other pages
     * @throws Exception\InvalidArgumentException if $relations is not an array
     *                                            or Traversable object
     * @return AbstractPage fluent interface, returns self
     */
    public function setRel($relations = null)
    {
        $this->rel = array();

        if (null !== $relations) {
            if ($relations instanceof Traversable) {
                $relations = ArrayUtils::iteratorToArray($relations);
            }

            if (!is_array($relations)) {
                throw new Exception\InvalidArgumentException(
                    'Invalid argument: $relations must be an ' .
                    'array or an instance of Traversable'
                );
            }

            foreach ($relations as $name => $relation) {
                if (is_string($name)) {
                    $this->rel[$name] = $relation;
                }
            }
        }

        return $this;
    }

    /**
     * Returns the page's forward links to other pages
     *
     * This method returns an associative array of forward links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  string $relation [optional] name of relation to return. If not
     *                          given, all relations will be returned.
     * @return array            an array of relations. If $relation is not
     *                          specified, all relations will be returned in
     *                          an associative array.
     */
    public function getRel($relation = null)
    {
        if (null !== $relation) {
            return isset($this->rel[$relation])
                ? $this->rel[$relation]
                : null;
        }

        return $this->rel;
    }

    /**
     * Sets the page's reverse links to other pages
     *
     * This method expects an associative array of reverse links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  array|Traversable $relations [optional] an associative array of
     *                                      reverse links to other pages
     *
     * @throws Exception\InvalidArgumentException if $relations it not an array
     *                                            or Traversable object
     * @return AbstractPage fluent interface, returns self
     */
    public function setRev($relations = null)
    {
        $this->rev = array();

        if (null !== $relations) {
            if ($relations instanceof Traversable) {
                $relations = ArrayUtils::iteratorToArray($relations);
            }

            if (!is_array($relations)) {
                throw new Exception\InvalidArgumentException(
                    'Invalid argument: $relations must be an ' .
                    'array or an instance of Traversable'
                );
            }

            foreach ($relations as $name => $relation) {
                if (is_string($name)) {
                    $this->rev[$name] = $relation;
                }
            }
        }

        return $this;
    }

    /**
     * Returns the page's reverse links to other pages
     *
     * This method returns an associative array of forward links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  string $relation  [optional] name of relation to return. If not
     *                           given, all relations will be returned.
     *
     * @return array             an array of relations. If $relation is not
     *                           specified, all relations will be returned in
     *                           an associative array.
     */
    public function getRev($relation = null)
    {
        if (null !== $relation) {
            return isset($this->rev[$relation])
                ?
                $this->rev[$relation]
                :
                null;
        }

        return $this->rev;
    }

    /**
     * Sets page order to use in parent container
     *
     * @param  int $order [optional] page order in container.
     *                    Default is null, which sets no
     *                    specific order.
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException if order is not integer or null
     */
    public function setOrder($order = null)
    {
        if (is_string($order)) {
            $temp = (int) $order;
            if ($temp < 0 || $temp > 0 || $order == '0') {
                $order = $temp;
            }
        }

        if (null !== $order && !is_int($order)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $order must be an integer or null, ' .
                'or a string that casts to an integer'
            );
        }

        $this->order = $order;

        // notify parent, if any
        if (isset($this->parent)) {
            $this->parent->notifyOrderUpdated();
        }

        return $this;
    }

    /**
     * Returns page order used in parent container
     *
     * @return int|null  page order or null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets ACL resource associated with this page
     *
     * @param  string|AclResource $resource [optional] resource to associate
     *                                      with page. Default is null, which
     *                                      sets no resource.
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException if $resource is invalid
     */
    public function setResource($resource = null)
    {
        if (null === $resource
            || is_string($resource)
            || $resource instanceof AclResource
        ) {
            $this->resource = $resource;
        } else {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $resource must be null, a string, ' .
                'or an instance of Zend\Permissions\Acl\Resource\ResourceInterface'
            );
        }

        return $this;
    }

    /**
     * Returns ACL resource associated with this page
     *
     * @return string|AclResource|null  ACL resource or null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets ACL privilege associated with this page
     *
     * @param  string|null $privilege  [optional] ACL privilege to associate
     *                                 with this page. Default is null, which
     *                                 sets no privilege.
     *
     * @return AbstractPage fluent interface, returns self
     */
    public function setPrivilege($privilege = null)
    {
        $this->privilege = is_string($privilege) ? $privilege : null;
        return $this;
    }

    /**
     * Returns ACL privilege associated with this page
     *
     * @return string|null  ACL privilege or null
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * Sets permission associated with this page
     *
     * @param  mixed|null $permission  [optional] permission to associate
     *                                  with this page. Default is null, which
     *                                  sets no permission.
     *
     * @return AbstractPage fluent interface, returns self
     */
    public function setPermission($permission = null)
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Returns permission associated with this page
     *
     * @return mixed|null  permission or null
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Sets whether page should be considered active or not
     *
     * @param  bool $active [optional] whether page should be
     *                      considered active or not. Default is true.
     *
     * @return AbstractPage fluent interface, returns self
     */
    public function setActive($active = true)
    {
        $this->active = (bool) $active;
        return $this;
    }

    /**
     * Returns whether page should be considered active or not
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default is
     *                          false.
     * @return bool             whether page should be considered active
     */
    public function isActive($recursive = false)
    {
        if (!$this->active && $recursive) {
            foreach ($this->pages as $page) {
                if ($page->isActive(true)) {
                    return true;
                }
            }
            return false;
        }

        return $this->active;
    }

    /**
     * Proxy to isActive()
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default
     *                          is false.
     *
     * @return bool             whether page should be considered active
     */
    public function getActive($recursive = false)
    {
        return $this->isActive($recursive);
    }

    /**
     * Sets whether the page should be visible or not
     *
     * @param  bool $visible [optional] whether page should be
     *                       considered visible or not. Default is true.
     * @return AbstractPage fluent interface, returns self
     */
    public function setVisible($visible = true)
    {
        if (is_string($visible) && 'false' == strtolower($visible)) {
            $visible = false;
        }
        $this->visible = (bool) $visible;
        return $this;
    }

    /**
     * Returns a boolean value indicating whether the page is visible
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          invisible if parent is invisible. Default is
     *                          false.
     *
     * @return bool             whether page should be considered visible
     */
    public function isVisible($recursive = false)
    {
        if ($recursive
            && isset($this->parent)
            && $this->parent instanceof self
        ) {
            if (!$this->parent->isVisible(true)) {
                return false;
            }
        }

        return $this->visible;
    }

    /**
     * Proxy to isVisible()
     *
     * Returns a boolean value indicating whether the page is visible
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          invisible if parent is invisible. Default is
     *                          false.
     *
     * @return bool             whether page should be considered visible
     */
    public function getVisible($recursive = false)
    {
        return $this->isVisible($recursive);
    }

    /**
     * Sets parent container
     *
     * @param  AbstractContainer $parent [optional] new parent to set.
     *                           Default is null which will set no parent.
     * @throws Exception\InvalidArgumentException
     * @return AbstractPage fluent interface, returns self
     */
    public function setParent(AbstractContainer $parent = null)
    {
        if ($parent === $this) {
            throw new Exception\InvalidArgumentException(
                'A page cannot have itself as a parent'
            );
        }

        // return if the given parent already is parent
        if ($parent === $this->parent) {
            return $this;
        }

        // remove from old parent
        if (null !== $this->parent) {
            $this->parent->removePage($this);
        }

        // set new parent
        $this->parent = $parent;

        // add to parent if page and not already a child
        if (null !== $this->parent && !$this->parent->hasPage($this, false)) {
            $this->parent->addPage($this);
        }

        return $this;
    }

    /**
     * Returns parent container
     *
     * @return AbstractContainer|null  parent container or null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the given property
     *
     * If the given property is native (id, class, title, etc), the matching
     * set method will be used. Otherwise, it will be set as a custom property.
     *
     * @param  string $property property name
     * @param  mixed  $value    value to set
     * @return AbstractPage fluent interface, returns self
     * @throws Exception\InvalidArgumentException if property name is invalid
     */
    public function set($property, $value)
    {
        if (!is_string($property) || empty($property)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $property must be a non-empty string'
            );
        }

        $method = 'set' . static::normalizePropertyName($property);

        if ($method != 'setOptions' && method_exists($this, $method)
        ) {
            $this->$method($value);
        } else {
            $this->properties[$property] = $value;
        }

        return $this;
    }

    /**
     * Returns the value of the given property
     *
     * If the given property is native (id, class, title, etc), the matching
     * get method will be used. Otherwise, it will return the matching custom
     * property, or null if not found.
     *
     * @param  string $property property name
     * @return mixed            the property's value or null
     * @throws Exception\InvalidArgumentException if property name is invalid
     */
    public function get($property)
    {
        if (!is_string($property) || empty($property)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $property must be a non-empty string'
            );
        }

        $method = 'get' . static::normalizePropertyName($property);

        if (method_exists($this, $method)) {
            return $this->$method();
        } elseif (isset($this->properties[$property])) {
            return $this->properties[$property];
        }

        return null;
    }

    // Magic overloads:

    /**
     * Sets a custom property
     *
     * Magic overload for enabling <code>$page->propname = $value</code>.
     *
     * @param  string $name  property name
     * @param  mixed  $value value to set
     * @return void
     * @throws Exception\InvalidArgumentException if property name is invalid
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Returns a property, or null if it doesn't exist
     *
     * Magic overload for enabling <code>$page->propname</code>.
     *
     * @param  string $name property name
     * @return mixed        property value or null
     * @throws Exception\InvalidArgumentException if property name is invalid
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Checks if a property is set
     *
     * Magic overload for enabling <code>isset($page->propname)</code>.
     *
     * Returns true if the property is native (id, class, title, etc), and
     * true or false if it's a custom property (depending on whether the
     * property actually is set).
     *
     * @param  string $name property name
     * @return bool whether the given property exists
     */
    public function __isset($name)
    {
        $method = 'get' . static::normalizePropertyName($name);
        if (method_exists($this, $method)) {
            return true;
        }

        return isset($this->properties[$name]);
    }

    /**
     * Unsets the given custom property
     *
     * Magic overload for enabling <code>unset($page->propname)</code>.
     *
     * @param  string $name property name
     * @return void
     * @throws Exception\InvalidArgumentException  if the property is native
     */
    public function __unset($name)
    {
        $method = 'set' . static::normalizePropertyName($name);
        if (method_exists($this, $method)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Unsetting native property "%s" is not allowed',
                    $name
                )
            );
        }

        if (isset($this->properties[$name])) {
            unset($this->properties[$name]);
        }
    }

    /**
     * Returns page label
     *
     * Magic overload for enabling <code>echo $page</code>.
     *
     * @return string  page label
     */
    public function __toString()
    {
        return $this->label;
    }

    // Public methods:

    /**
     * Adds a forward relation to the page
     *
     * @param  string $relation relation name (e.g. alternate, glossary,
     *                          canonical, etc)
     * @param  mixed  $value    value to set for relation
     * @return AbstractPage  fluent interface, returns self
     */
    public function addRel($relation, $value)
    {
        if (is_string($relation)) {
            $this->rel[$relation] = $value;
        }
        return $this;
    }

    /**
     * Adds a reverse relation to the page
     *
     * @param  string $relation relation name (e.g. alternate, glossary,
     *                          canonical, etc)
     * @param  mixed  $value    value to set for relation
     * @return AbstractPage fluent interface, returns self
     */
    public function addRev($relation, $value)
    {
        if (is_string($relation)) {
            $this->rev[$relation] = $value;
        }
        return $this;
    }

    /**
     * Removes a forward relation from the page
     *
     * @param  string $relation name of relation to remove
     * @return AbstractPage fluent interface, returns self
     */
    public function removeRel($relation)
    {
        if (isset($this->rel[$relation])) {
            unset($this->rel[$relation]);
        }

        return $this;
    }

    /**
     * Removes a reverse relation from the page
     *
     * @param  string $relation name of relation to remove
     * @return AbstractPage  fluent interface, returns self
     */
    public function removeRev($relation)
    {
        if (isset($this->rev[$relation])) {
            unset($this->rev[$relation]);
        }

        return $this;
    }

    /**
     * Returns an array containing the defined forward relations
     *
     * @return array  defined forward relations
     */
    public function getDefinedRel()
    {
        return array_keys($this->rel);
    }

    /**
     * Returns an array containing the defined reverse relations
     *
     * @return array  defined reverse relations
     */
    public function getDefinedRev()
    {
        return array_keys($this->rev);
    }

    /**
     * Returns custom properties as an array
     *
     * @return array  an array containing custom properties
     */
    public function getCustomProperties()
    {
        return $this->properties;
    }

    /**
     * Returns a hash code value for the page
     *
     * @return string  a hash code value for this page
     */
    final public function hashCode()
    {
        return spl_object_hash($this);
    }

    /**
     * Returns an array representation of the page
     *
     * @return array  associative array containing all page properties
     */
    public function toArray()
    {
        return array_merge($this->getCustomProperties(), array(
            'label'     => $this->getLabel(),
            'fragment'  => $this->getFragment(),
            'id'        => $this->getId(),
            'class'     => $this->getClass(),
            'title'     => $this->getTitle(),
            'target'    => $this->getTarget(),
            'rel'       => $this->getRel(),
            'rev'       => $this->getRev(),
            'order'     => $this->getOrder(),
            'resource'  => $this->getResource(),
            'privilege' => $this->getPrivilege(),
            'permission' => $this->getPermission(),
            'active'    => $this->isActive(),
            'visible'   => $this->isVisible(),
            'type'      => get_class($this),
            'pages'     => parent::toArray(),
        ));
    }

    // Internal methods:

    /**
     * Normalizes a property name
     *
     * @param  string $property  property name to normalize
     * @return string            normalized property name
     */
    protected static function normalizePropertyName($property)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
    }

    // Abstract methods:

    /**
     * Returns href for this page
     *
     * @return string  the page's href
     */
    abstract public function getHref();
}
