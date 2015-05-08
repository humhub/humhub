<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

use Zend\Code\Annotation;
use Zend\Code\Exception;
use Zend\Code\NameInformation;

class PropertyScanner implements ScannerInterface
{
    /**
     * @var bool
     */
    protected $isScanned = false;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var NameInformation
     */
    protected $nameInformation;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ClassScanner
     */
    protected $scannerClass;

    /**
     * @var int
     */
    protected $lineStart;

    /**
     * @var bool
     */
    protected $isProtected = false;

    /**
     * @var bool
     */
    protected $isPublic = true;

    /**
     * @var bool
     */
    protected $isPrivate = false;

    /**
     * @var bool
     */
    protected $isStatic = false;

    /**
     * @var string
     */
    protected $docComment;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * Constructor
     *
     * @param array $propertyTokens
     * @param NameInformation $nameInformation
     */
    public function __construct(array $propertyTokens, NameInformation $nameInformation = null)
    {
        $this->tokens = $propertyTokens;
        $this->nameInformation = $nameInformation;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @param ClassScanner $scannerClass
     */
    public function setScannerClass(ClassScanner $scannerClass)
    {
        $this->scannerClass = $scannerClass;
    }

    /**
     * @return ClassScanner
     */
    public function getClassScanner()
    {
        return $this->scannerClass;
    }

    /**
     * @return string
     */
    public function getName()
    {
        $this->scan();
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        $this->scan();
        return $this->isPublic;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        $this->scan();
        return $this->isPrivate;
    }

    /**
     * @return bool
     */
    public function isProtected()
    {
        $this->scan();
        return $this->isProtected;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        $this->scan();
        return $this->isStatic;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $this->scan();
        return $this->value;
    }

    /**
     * @return string
     */
    public function getDocComment()
    {
        $this->scan();
        return $this->docComment;
    }

    /**
     * @param Annotation\AnnotationManager $annotationManager
     * @return AnnotationScanner
     */
    public function getAnnotations(Annotation\AnnotationManager $annotationManager)
    {
        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        return new AnnotationScanner($annotationManager, $docComment, $this->nameInformation);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $this->scan();
        return var_export($this, true);
    }

    /**
     * Scan tokens
     *
     * @throws \Zend\Code\Exception\RuntimeException
     */
    protected function scan()
    {
        if ($this->isScanned) {
            return;
        }

        if (!$this->tokens) {
            throw new Exception\RuntimeException('No tokens were provided');
        }

        /**
         * Variables & Setup
         */
        $tokens = &$this->tokens;

        reset($tokens);

        SCANNER_TOP:

        $token = current($tokens);

        if (!is_string($token)) {
            list($tokenType, $tokenContent, $tokenLine) = $token;

            switch ($tokenType) {
                case T_DOC_COMMENT:
                    if ($this->docComment === null && $this->name === null) {
                        $this->docComment = $tokenContent;
                    }
                    goto SCANNER_CONTINUE;

                case T_VARIABLE:
                    $this->name = ltrim($tokenContent, '$');
                    goto SCANNER_CONTINUE;

                case T_PUBLIC:
                    // use defaults
                    goto SCANNER_CONTINUE;

                case T_PROTECTED:
                    $this->isProtected = true;
                    $this->isPublic = false;
                    goto SCANNER_CONTINUE;

                case T_PRIVATE:
                    $this->isPrivate = true;
                    $this->isPublic = false;
                    goto SCANNER_CONTINUE;

                case T_STATIC:
                    $this->isStatic = true;
                    goto SCANNER_CONTINUE;

                default:
                    if ($this->name !== null && trim($tokenContent) !== '') {
                        $this->value .= (is_string($token)) ? $token : $tokenContent;
                        if (substr($this->value, 0, 1) === '"' || substr($this->value, 0, 1) === "'") {
                            $this->value = substr($this->value, 1, -1); // Remove quotes
                        }
                    }
                    goto SCANNER_CONTINUE;
            }
        }

        SCANNER_CONTINUE:

        if (next($this->tokens) === false) {
            goto SCANNER_END;
        }
        goto SCANNER_TOP;

        SCANNER_END:

        $this->isScanned = true;
    }
}
