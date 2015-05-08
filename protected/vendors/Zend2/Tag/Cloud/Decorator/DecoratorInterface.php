<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Tag\Cloud\Decorator;

/**
 * Interface for decorators
 */
interface DecoratorInterface
{
    /**
     * Constructor
     *
     * Allow passing options to the constructor.
     *
     * @param  mixed $options
     */
    public function __construct($options = null);

    /**
     * Render a list of tags
     *
     * @param  mixed $tags
     * @return string
     */
    public function render($tags);
}
