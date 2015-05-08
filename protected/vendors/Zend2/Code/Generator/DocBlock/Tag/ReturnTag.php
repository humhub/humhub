<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock\Tag;

use Zend\Code\Generator\DocBlock\Tag;
use Zend\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionDocBlockTag;

class ReturnTag extends Tag
{
    /**
     * @var string
     */
    protected $datatype = null;

    /**
     * @param  ReflectionDocBlockTag $reflectionTagReturn
     * @return ReturnTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagReturn)
    {
        $returnTag = new static();
        $returnTag
            ->setName('return')
            ->setDatatype($reflectionTagReturn->getType()) // @todo rename
            ->setDescription($reflectionTagReturn->getDescription());

        return $returnTag;
    }

    /**
     * @param  string $datatype
     * @return ReturnTag
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@return ' . $this->datatype . ' ' . $this->description;
    }
}
