<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Tag;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class Cloud
{
    /**
     * DecoratorInterface for the cloud
     *
     * @var Cloud\Decorator\AbstractCloud
     */
    protected $cloudDecorator = null;

    /**
     * DecoratorInterface for the tags
     *
     * @var Cloud\Decorator\AbstractTag
     */
    protected $tagDecorator = null;

    /**
     * List of all tags
     *
     * @var ItemList
     */
    protected $tags = null;

    /**
     * Plugin manager for decorators
     *
     * @var Cloud\DecoratorPluginManager
     */
    protected $decorators = null;

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $skipOptions = array(
        'options',
        'config',
    );

    /**
     * Create a new tag cloud with options
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options from array
     *
     * @param  array $options Configuration for Cloud
     * @return Cloud
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->skipOptions)) {
                continue;
            }

            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Set the tags for the tag cloud.
     *
     * $tags should be an array containing single tags as array. Each tag
     * array should at least contain the keys 'title' and 'weight'. Optionally
     * you may supply the key 'url', to which the tag links to. Any additional
     * parameter in the array is silently ignored and can be used by custom
     * decorators.
     *
     * @param  array $tags
     * @throws Exception\InvalidArgumentException
     * @return Cloud
     */
    public function setTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->appendTag($tag);
        }
        return $this;
    }

    /**
     * Append a single tag to the cloud
     *
     * @param  TaggableInterface|array $tag
     * @throws Exception\InvalidArgumentException
     * @return Cloud
     */
    public function appendTag($tag)
    {
        $tags = $this->getItemList();

        if ($tag instanceof TaggableInterface) {
            $tags[] = $tag;
            return $this;
        }

        if (!is_array($tag)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Tag must be an instance of %s\TaggableInterface or an array; received "%s"',
                __NAMESPACE__,
                (is_object($tag) ? get_class($tag) : gettype($tag))
            ));
        }

        $tags[] = new Item($tag);

        return $this;
    }

    /**
     * Set the item list
     *
     * @param  ItemList $itemList
     * @return Cloud
     */
    public function setItemList(ItemList $itemList)
    {
        $this->tags = $itemList;
        return $this;
    }

    /**
     * Retrieve the item list
     *
     * If item list is undefined, creates one.
     *
     * @return ItemList
     */
    public function getItemList()
    {
        if (null === $this->tags) {
            $this->setItemList(new ItemList());
        }
        return $this->tags;
    }

    /**
     * Set the decorator for the cloud
     *
     * @param  mixed $decorator
     * @throws Exception\InvalidArgumentException
     * @return Cloud
     */
    public function setCloudDecorator($decorator)
    {
        $options = null;

        if (is_array($decorator)) {
            if (isset($decorator['options'])) {
                $options = $decorator['options'];
            }

            if (isset($decorator['decorator'])) {
                $decorator = $decorator['decorator'];
            }
        }

        if (is_string($decorator)) {
            $decorator = $this->getDecoratorPluginManager()->get($decorator, $options);
        }

        if (!($decorator instanceof Cloud\Decorator\AbstractCloud)) {
            throw new Exception\InvalidArgumentException('DecoratorInterface is no instance of Cloud\Decorator\AbstractCloud');
        }

        $this->cloudDecorator = $decorator;

        return $this;
    }

    /**
     * Get the decorator for the cloud
     *
     * @return Cloud\Decorator\AbstractCloud
     */
    public function getCloudDecorator()
    {
        if (null === $this->cloudDecorator) {
            $this->setCloudDecorator('htmlCloud');
        }
        return $this->cloudDecorator;
    }

    /**
     * Set the decorator for the tags
     *
     * @param  mixed $decorator
     * @throws Exception\InvalidArgumentException
     * @return Cloud
     */
    public function setTagDecorator($decorator)
    {
        $options = null;

        if (is_array($decorator)) {
            if (isset($decorator['options'])) {
                $options = $decorator['options'];
            }

            if (isset($decorator['decorator'])) {
                $decorator = $decorator['decorator'];
            }
        }

        if (is_string($decorator)) {
            $decorator = $this->getDecoratorPluginManager()->get($decorator, $options);
        }

        if (!($decorator instanceof Cloud\Decorator\AbstractTag)) {
            throw new Exception\InvalidArgumentException('DecoratorInterface is no instance of Cloud\Decorator\AbstractTag');
        }

        $this->tagDecorator = $decorator;

        return $this;
    }

    /**
     * Get the decorator for the tags
     *
     * @return Cloud\Decorator\AbstractTag
     */
    public function getTagDecorator()
    {
        if (null === $this->tagDecorator) {
            $this->setTagDecorator('htmlTag');
        }
        return $this->tagDecorator;
    }

    /**
     * Set plugin manager for use with decorators
     *
     * @param  Cloud\DecoratorPluginManager $decorators
     * @return Cloud
     */
    public function setDecoratorPluginManager(Cloud\DecoratorPluginManager $decorators)
    {
        $this->decorators = $decorators;
        return $this;
    }

    /**
     * Get the plugin manager for decorators
     *
     * @return Cloud\DecoratorPluginManager
     */
    public function getDecoratorPluginManager()
    {
        if ($this->decorators === null) {
            $this->decorators = new Cloud\DecoratorPluginManager();
        }

        return $this->decorators;
    }

    /**
     * Render the tag cloud
     *
     * @return string
     */
    public function render()
    {
        $tags = $this->getItemList();

        if (count($tags) === 0) {
            return '';
        }

        $tagsResult  = $this->getTagDecorator()->render($tags);
        $cloudResult = $this->getCloudDecorator()->render($tagsResult);

        return $cloudResult;
    }

    /**
     * Render the tag cloud
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $result = $this->render();
            return $result;
        } catch (\Exception $e) {
            $message = "Exception caught by tag cloud: " . $e->getMessage()
                     . "\nStack Trace:\n" . $e->getTraceAsString();
            trigger_error($message, E_USER_WARNING);
            return '';
        }
    }
}
