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

use phpDocumentor\Event\Dispatcher;
use phpDocumentor\Parser\Event\LogEvent;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Location;
use phpDocumentor\Reflection\Event\PostDocBlockExtractionEvent;
use phpDocumentor\Reflection\Exception;
use Psr\Log\LogLevel;
use PHPParser_Comment_Doc;
use PHPParser_Node;
use PHPParser_Node_Const;
use PHPParser_Node_Expr_FuncCall;
use PHPParser_Node_Expr_Include;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_ClassConst;
use PHPParser_Node_Stmt_ClassMethod;
use PHPParser_Node_Stmt_Const;
use PHPParser_Node_Stmt_Function;
use PHPParser_Node_Stmt_InlineHTML;
use PHPParser_Node_Stmt_Interface;
use PHPParser_Node_Stmt_Property;
use PHPParser_Node_Stmt_PropertyProperty;
use PHPParser_Node_Stmt_Trait;
use PHPParser_Node_Stmt_UseUse;
use PHPParser_NodeVisitor;

/**
 * Reflection class for a full file.
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2012 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class FileReflector extends ReflectionAbstract implements PHPParser_NodeVisitor
{
    /** @var string An MD5 hashed representation of the contents of this file */
    protected $hash;

    /** @var string The contents of this file. */
    protected $contents = '';

    /** @var IncludeReflector[] */
    protected $includes = array();

    /** @var ConstantReflector[] */
    protected $constants = array();

    /** @var ClassReflector[] */
    protected $classes = array();

    /** @var TraitReflector[] */
    protected $traits = array();

    /** @var InterfaceReflector[] */
    protected $interfaces = array();

    /** @var FunctionReflector[] */
    protected $functions = array();

    /** @var string The name of the file associated with this reflection object. */
    protected $filename = '';

    /** @var DocBlock */
    protected $doc_block;

    /** @var string The package name that should be used if none is present in the file */
    protected $default_package_name = 'Default';

    /** @var string[] A list of markers contained in this file. */
    protected $markers = array();

    /** @var string[] A list of errors during processing */
    protected $parse_markers = array();

    /** @var string[] A list of all marker types to search for in this file. */
    protected $marker_terms = array('TODO', 'FIXME');

    /** @var Context */
    protected $context;

    /**
     * Opens the file and retrieves its contents.
     *
     * During construction the given file is checked whether it is readable and
     * if the $validate argument is true a PHP Lint action is executed to
     * check whether the there are no parse errors.
     *
     * By default the Lint check is disabled because of the performance hit
     * introduced by this action.
     *
     * If the validation checks out, the file's contents are read, converted to
     * UTF-8 and the object is created from those contents.
     *
     * @param string  $file     Name of the file.
     * @param boolean $validate Whether to check the file using PHP Lint.
     * @param string  $encoding The encoding of the file.
     *
     * @throws Exception\UnreadableFile If the filename is incorrect or
     *   the file cannot be opened
     * @throws Exception\UnparsableFile If the file fails PHP lint checking
     *   (this can only happen when $validate is set to true)
     */
    public function __construct($file, $validate = false, $encoding = 'utf-8')
    {
        if (!is_string($file) || (!is_readable($file))) {
            throw new Exception\UnreadableFile(
                'The given file should be a string, should exist on the filesystem and should be readable'
            );
        }

        if ($validate) {
            exec('php -l ' . escapeshellarg($file), $output, $result);
            if ($result != 0) {
                throw new Exception\UnparsableFile(
                    'The given file could not be interpreted as it contains errors: '
                    . implode(PHP_EOL, $output)
                );
            }
        }

        $this->filename = $file;
        $this->contents = file_get_contents($file);
        $this->context = new Context();

        if (strtolower($encoding) !== 'utf-8' && extension_loaded('iconv')) {
            $this->contents = iconv(
                strtolower($encoding),
                'utf-8//IGNORE//TRANSLIT',
                $this->contents
            );
        }

        // filemtime($file) is sometimes between 0.00001 and 0.00005 seconds
        // faster but md5 is more accurate. It can also result in false
        // positives or false negatives after copying or checking out a codebase.
        $this->hash = md5($this->contents);
    }

    public function process()
    {
        // with big fluent interfaces it can happen that PHP-Parser's Traverser
        // exceeds the 100 recursions limit; we set it to 10000 to be sure.
        ini_set('xdebug.max_nesting_level', 10000);

        $traverser = new Traverser();
        $traverser->addVisitor($this);
        $traverser->traverse($this->contents);

        $this->scanForMarkers();
    }

    /**
     * @return ClassReflector[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @return TraitReflector[]
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @return ConstantReflector[]
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * @return FunctionReflector[]
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * @return IncludeReflector[]
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * @return InterfaceReflector[]
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    public function beforeTraverse(array $nodes)
    {
        $node = null;
        $key = 0;
        foreach ($nodes as $k => $n) {
            if (!$n instanceof PHPParser_Node_Stmt_InlineHTML) {
                $node = $n;
                $key = $k;
                break;
            }
        }

        if ($node) {
            $comments = (array) $node->getAttribute('comments');

            // remove non-DocBlock comments
            $comments = array_values(
                array_filter(
                    $comments,
                    function ($comment) {
                        return $comment instanceof PHPParser_Comment_Doc;
                    }
                )
            );

            if (!empty($comments)) {
                try {
                    $docblock = new DocBlock(
                        (string) $comments[0],
                        null,
                        new Location($comments[0]->getLine())
                    );

                    // the first DocBlock in a file documents the file if
                    // * it precedes another DocBlock or
                    // * it contains a @package tag and doesn't precede a class
                    //   declaration or
                    // * it precedes a non-documentable element (thus no include,
                    //   require, class, function, define, const)
                    if (count($comments) > 1
                        || (!$node instanceof PHPParser_Node_Stmt_Class
                        && !$node instanceof PHPParser_Node_Stmt_Interface
                        && $docblock->hasTag('package'))
                        || !$this->isNodeDocumentable($node)
                    ) {
                        $this->doc_block = $docblock;

                        // remove the file level DocBlock from the node's comments
                        array_shift($comments);
                    }
                } catch (\Exception $e) {
                    $this->log($e->getMessage(), LogLevel::CRITICAL);
                }
            }

            // always update the comments attribute so that standard comments
            // do not stop DocBlock from being attached to an element
            $node->setAttribute('comments', $comments);
            $nodes[$key] = $node;
        }

        if (class_exists('phpDocumentor\Event\Dispatcher')) {
            Dispatcher::getInstance()->dispatch(
                'reflection.docblock-extraction.post',
                PostDocBlockExtractionEvent
                ::createInstance($this)->setDocblock($this->doc_block)
            );
        }

        return $nodes;
    }

    /**
     * Checks whether the given node is recogized by phpDocumentor as a
     * documentable element.
     *
     * The following elements are recognized:
     *
     * - Trait
     * - Class
     * - Interface
     * - Class constant
     * - Class method
     * - Property
     * - Include/Require
     * - Constant, both const and define
     * - Function
     *
     * @param PHPParser_Node $node
     *
     * @return bool
     */
    protected function isNodeDocumentable(PHPParser_Node $node)
    {
        return ($node instanceof PHPParser_Node_Stmt_Class)
            || ($node instanceof PHPParser_Node_Stmt_Interface)
            || ($node instanceof PHPParser_Node_Stmt_ClassConst)
            || ($node instanceof PHPParser_Node_Stmt_ClassMethod)
            || ($node instanceof PHPParser_Node_Stmt_Const)
            || ($node instanceof PHPParser_Node_Stmt_Function)
            || ($node instanceof PHPParser_Node_Stmt_Property)
            || ($node instanceof PHPParser_Node_Stmt_PropertyProperty)
            || ($node instanceof PHPParser_Node_Stmt_Trait)
            || ($node instanceof PHPParser_Node_Expr_Include)
            || ($node instanceof PHPParser_Node_Expr_FuncCall
            && ($node->name instanceof PHPParser_Node_Name)
            && $node->name == 'define');
    }

    public function enterNode(PHPParser_Node $node)
    {
    }

    public function getName()
    {
        return $this->filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getDocBlock()
    {
        return $this->doc_block;
    }

    public function getLineNumber()
    {
        return 0;
    }

    public function getDefaultPackageName()
    {
        return $this->default_package_name;
    }

    /**
     * Adds a marker to scan the contents of this file for.
     *
     * @param string $name The Marker term, e.g. FIXME or TODO.
     *
     * @return void
     */
    public function addMarker($name)
    {
        $this->marker_terms[] = $name;
    }

    /**
     * Sets a list of markers to search for.
     *
     * @param string[] $markers A list of marker terms to scan for.
     *
     * @see phpDocumentor\Reflection\FileReflector::addMarker()
     *
     * @return void
     */
    public function setMarkers(array $markers)
    {
        $this->marker_terms = array();

        foreach ($markers as $marker) {
            $this->addMarker($marker);
        }
    }

    public function getMarkers()
    {
        return $this->markers;
    }

    /**
     * Adds a parse error to the system
     *
     * @param LogEvent $data Contains the type,
     *     message, line and code element.
     *
     * @return void
     */
    public function addParserMarker($data)
    {
        $this->parse_markers[] = array(
            $data->getType(),
            $data->getMessage(),
            $data->getLine(),
            $data->getCode()
        );
    }

    /**
     * Scans the file for markers and records them in the markers property.
     *
     * @see getMarkers()
     *
     * @todo this method may incur a performance penalty while the AST also
     * contains the comments. This method should be replaced by a piece of
     * code that interprets the comments in the AST.
     * This has not been done since that may be an extensive refactoring (each
     * PHPParser_Node* contains a 'comments' attribute and must thus recursively
     * be discovered)
     *
     * @return void
     */
    public function scanForMarkers()
    {
        // find all markers, get the entire file and check for marker terms.
        $marker_data = array();
        foreach (explode("\n", $this->contents) as $line_number => $line) {
            preg_match_all(
                '~//[\s]*(' . implode('|', $this->marker_terms) . ')\:?[\s]*(.*)~',
                $line,
                $matches,
                PREG_SET_ORDER
            );
            foreach ($matches as &$match) {
                $match[3] = $line_number + 1;
            }
            $marker_data = array_merge($marker_data, $matches);
        }

        // store marker results and remove first entry (entire match),
        // this results in an array with 2 entries:
        // marker name and content
        $this->markers = $marker_data;
        foreach ($this->markers as &$marker) {
            array_shift($marker);
        }
    }

    public function getParseErrors()
    {
        return $this->parse_markers;
    }

    public function getNamespace()
    {
        return $this->context->getNamespace();
    }

    public function getNamespaceAliases()
    {
        return $this->context->getNamespaceAliases();
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function setDefaultPackageName($default_package_name)
    {
        $this->default_package_name = $default_package_name;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function leaveNode(PHPParser_Node $node)
    {
        $prettyPrinter = new PrettyPrinter;

        switch (get_class($node)) {
            case 'PHPParser_Node_Stmt_Use':
                /** @var PHPParser_Node_Stmt_UseUse $use */
                foreach ($node->uses as $use) {
                    $this->context->setNamespaceAlias(
                        $use->alias,
                        implode('\\', $use->name->parts)
                    );
                }
                break;
            case 'PHPParser_Node_Stmt_Namespace':
                $this->context->setNamespace(
                    isset($node->name) && ($node->name) ? implode('\\', $node->name->parts) : ''
                );
                break;
            case 'PHPParser_Node_Stmt_Class':
                $class = new ClassReflector($node, $this->context);
                $class->parseSubElements();
                $this->classes[] = $class;
                break;
            case 'PHPParser_Node_Stmt_Trait':
                $trait = new TraitReflector($node, $this->context);
                $trait->parseSubElements();
                $this->traits[] = $trait;
                break;
            case 'PHPParser_Node_Stmt_Interface':
                $interface = new InterfaceReflector($node, $this->context);
                $interface->parseSubElements();
                $this->interfaces[] = $interface;
                break;
            case 'PHPParser_Node_Stmt_Function':
                $function = new FunctionReflector($node, $this->context);
                $this->functions[] = $function;
                break;
            case 'PHPParser_Node_Stmt_Const':
                foreach ($node->consts as $constant) {
                    $reflector = new ConstantReflector(
                        $node,
                        $this->context,
                        $constant
                    );
                    $this->constants[] = $reflector;
                }
                break;
            case 'PHPParser_Node_Expr_FuncCall':
                if (($node->name instanceof PHPParser_Node_Name)
                    && ($node->name == 'define')
                    && isset($node->args[0])
                    && isset($node->args[1])
                ) {
                    // transform the first argument of the define function call into a constant name
                    $name = str_replace(
                        array('\\\\', '"', "'"),
                        array('\\', '', ''),
                        trim($prettyPrinter->prettyPrintExpr($node->args[0]->value), '\'')
                    );
                    $nameParts = explode('\\', $name);
                    $shortName = end($nameParts);

                    $constant = new PHPParser_Node_Const($shortName, $node->args[1]->value, $node->getAttributes());
                    $constant->namespacedName = new PHPParser_Node_Name($name);

                    $constant_statement = new \PHPParser_Node_Stmt_Const(array($constant));
                    $constant_statement->setAttribute('comments', array($node->getDocComment()));
                    $this->constants[] = new ConstantReflector($constant_statement, $this->context, $constant);
                }
                break;
            case 'PHPParser_Node_Expr_Include':
                $include = new IncludeReflector($node, $this->context);
                $this->includes[] = $include;
                break;
        }
    }

    public function afterTraverse(array $nodes)
    {
    }
}
