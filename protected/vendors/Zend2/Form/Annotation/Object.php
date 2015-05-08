<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Form\Annotation;


/**
 * Object annotation
 *
 * Use this annotation to specify an object to use as the bound object of a form or fieldset
 *
 * @Annotation
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Object extends AbstractStringAnnotation
{
    /**
     * Retrieve the object
     *
     * @return null|string
     */
    public function getObject()
    {
        return $this->value;
    }
}
