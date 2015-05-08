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

class AuthorTag extends Tag
{
    /**
     * @var string
     */
    protected $datatype = null;

    /**
     * @var string
     */
    protected $paramName = null;

    /**
     * @param  ReflectionDocBlockTag $reflectionTagParam
     * @return AuthorTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagParam)
    {
        $authorTag = new self();
        $authorTag
            ->setName('author')
            ->setAuthorName($reflectionTagParam->getType()) // @todo rename
            ->setAuthorEmail($reflectionTagParam->getVariableName())
            ->setDescription($reflectionTagParam->getDescription());

        return $authorTag;
    }

    /**
     * @param  string $datatype
     * @return AuthorTag
     */
    public function setDatatype($datatype)
    {
        $this->datatype = (string) $datatype;
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
     * @param  string $paramName
     * @return AuthorTag
     */
    public function setParamName($paramName)
    {
        $this->paramName = (string) $paramName;
        return $this;
    }

    /**
     * @return string
     */
    public function getParamName()
    {
        return $this->paramName;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@param '
            . (($this->datatype != null) ? $this->datatype : 'unknown')
            . (($this->paramName != null) ? ' $' . $this->paramName : '')
            . (($this->description != null) ? ' ' . $this->description : '');

        return $output;
    }

}
