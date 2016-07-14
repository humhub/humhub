<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2012 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

use Exception;
use InvalidArgumentException;
use phpDocumentor\Event\Dispatcher;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Location;
use phpDocumentor\Reflection\Event\PostDocBlockExtractionEvent;
use Psr\Log\LogLevel;
use PHPParser_Node_Expr;
use PHPParser_Node_Stmt;
use PHPParser_NodeAbstract;
use PHPParser_PrettyPrinterAbstract;

/**
 * Basic reflection providing support for events and basic properties as a
 * DocBlock and names.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
abstract class BaseReflector extends ReflectionAbstract
{
    /** @var PHPParser_Node_Stmt */
    protected $node;

    /**
     * The package name that is passed on by the parent Reflector.
     *
     * May be overwritten and should be passed on to children supporting
     * packages.
     *
     * @var string
     */
    protected $default_package_name = '';

    /**
     * PHP AST pretty printer used to get representations of values.
     *
     * @var PHPParser_PrettyPrinterAbstract
     */
    protected static $prettyPrinter = null;

    /**
     * Initializes this reflector with the correct node as produced by
     * PHP-Parser.
     *
     * @param PHPParser_NodeAbstract $node
     * @param Context                $context
     *
     * @link http://github.com/nikic/PHP-Parser
     */
    public function __construct(PHPParser_NodeAbstract $node, Context $context)
    {
        $this->node = $node;
        $context->setLSEN($this->getLSEN());
        $this->context = $context;
    }

    /**
     * Returns the current PHP-Parser node that holds more detailed information
     * about the reflected object. e.g. position in the file and further attributes.
     * @return PHPParser_Node_Stmt|PHPParser_NodeAbstract
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Sets the name for the namespace.
     *
     * @param string $namespace
     *
     * @throws InvalidArgumentException if something other than a string is
     *     passed.
     *
     * @return void
     */
    public function setNamespace($namespace)
    {
        if (!is_string($namespace)) {
            throw new InvalidArgumentException(
                'Expected a string for the namespace'
            );
        }

        $this->context->setNamespace($namespace);
    }

    /**
     * Returns the parsed DocBlock.
     *
     * @return DocBlock|null
     */
    public function getDocBlock()
    {
        return $this->extractDocBlock($this->node);
    }

    /**
     * Extracts a parsed DocBlock from an object.
     *
     * @param object $node Any object with a "getDocComment()" method.
     *
     * @return DocBlock|null
     */
    protected function extractDocBlock($node)
    {
        $doc_block = null;
        $comment = $node->getDocComment();
        if ($comment) {
            try {
                $doc_block = new DocBlock(
                    (string) $comment,
                    $this->context,
                    new Location($comment->getLine())
                );
            } catch (Exception $e) {
                $this->log($e->getMessage(), LogLevel::CRITICAL);
            }
        }

        if (class_exists('phpDocumentor\Event\Dispatcher')) {
            Dispatcher::getInstance()->dispatch(
                'reflection.docblock-extraction.post',
                PostDocBlockExtractionEvent
                ::createInstance($this)->setDocblock($doc_block)
            );
        }

        return $doc_block;
    }

    /**
     * Returns the name for this Reflector instance.
     *
     * @return string
     */
    public function getName()
    {
        if (isset($this->node->namespacedName)) {
            return '\\'.implode('\\', $this->node->namespacedName->parts);
        }

        return $this->getShortName();
    }

    /**
     * Returns the last component of a namespaced name as a short form.
     *
     * @return string
     */
    public function getShortName()
    {
        return isset($this->node->name)
            ? $this->node->name
            : (string) $this->node;
    }

    /**
     * Gets the LSEN.
     *
     * Returns this element's Local Structural Element Name (LSEN). This name
     * consistents of the element's short name, along with punctuation that
     * hints at the kind of structural element. If the structural element is
     * part of a type (i.e. an interface/trait/class' property/method/constant),
     * it also contains the name of the owning type.
     *
     * @return string
     */
    public function getLSEN()
    {
        return '';
    }

    /**
     * Returns the namespace name for this object.
     *
     * If this object does not have a namespace then the word 'global' is
     * returned to indicate a global namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        if (!$this->node->namespacedName) {
            return $this->context->getNamespace();
        }

        $parts = $this->node->namespacedName->parts;
        array_pop($parts);

        $namespace = implode('\\', $parts);

        return $namespace ? $namespace : 'global';
    }

    /**
     * Returns a listing of namespace aliases where the key represents the alias
     * and the value the Fully Qualified Namespace Name.
     *
     * @return string[]
     */
    public function getNamespaceAliases()
    {
        return $this->context->getNamespaceAliases();
    }

    /**
     * Sets a listing of namespace aliases.
     *
     * The keys represents the alias name and the value the
     * Fully Qualified Namespace Name (FQNN).
     *
     * @param string[] $aliases
     *
     * @return void
     */
    public function setNamespaceAliases(array $aliases)
    {
        $this->context->setNamespaceAliases($aliases);
    }

    /**
     * Sets the Fully Qualified Namespace Name (FQNN) for an alias.
     *
     * @param string $alias
     * @param string $fqnn
     *
     * @return void
     */
    public function setNamespaceAlias($alias, $fqnn)
    {
        $this->context->setNamespaceAlias($alias, $fqnn);
    }

    /**
     * Returns the line number where this object starts.
     *
     * @return int
     */
    public function getLinenumber()
    {
        return $this->node->getLine();
    }

    /**
     * Sets the default package name for this object.
     *
     * If the DocBlock contains a different package name then that overrides
     * this package name.
     *
     * @param string $default_package_name The name of the package as defined
     *     in the PHPDoc Standard.
     *
     * @return void
     */
    public function setDefaultPackageName($default_package_name)
    {
        $this->default_package_name = $default_package_name;
    }

    /**
     * Returns the package name that is default for this element.
     *
     * This value may change after the DocBlock is interpreted. If that contains
     * a package tag then that tag overrides the Default package name.
     *
     * @return string
     */
    public function getDefaultPackageName()
    {
        return $this->default_package_name;
    }

    /**
     * Returns a simple human readable output for a value.
     *
     * @param PHPParser_Node_Expr $value The value node as provided by
     *     PHP-Parser.
     *
     * @return string
     */
    protected function getRepresentationOfValue(
        PHPParser_Node_Expr $value = null
    ) {
        if (null === $value) {
            return '';
        }

        if (!self::$prettyPrinter) {
            self::$prettyPrinter = new PrettyPrinter();
        }

        return self::$prettyPrinter->prettyPrintExpr($value);
    }
}
