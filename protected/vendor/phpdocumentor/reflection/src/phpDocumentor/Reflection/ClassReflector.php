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

use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_TraitUse;

/**
 * Provides static reflection for a class.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ClassReflector extends InterfaceReflector
{
    /** @var \PHPParser_Node_Stmt_Class */
    protected $node;

    /** @var string[] */
    protected $traits = array();

    public function parseSubElements()
    {
        /** @var \PHPParser_Node_Stmt_TraitUse $stmt  */
        foreach ($this->node->stmts as $stmt) {
            if ($stmt instanceof \PHPParser_Node_Stmt_TraitUse) {
                foreach ($stmt->traits as $trait) {
                    $this->traits[] = '\\' . (string) $trait;
                }
            }
        }

        parent::parseSubElements();
    }

    /**
     * Returns whether this is an abstract class.
     *
     * @return bool
     */
    public function isAbstract()
    {
        return (bool) ($this->node->type & PHPParser_Node_Stmt_Class::MODIFIER_ABSTRACT);
    }

    /**
     * Returns whether this class is final and thus cannot be extended.
     *
     * @return bool
     */
    public function isFinal()
    {
        return (bool) ($this->node->type & PHPParser_Node_Stmt_Class::MODIFIER_FINAL);
    }

    /**
     * Returns a list of the names of traits used in this class.
     *
     * @return string[]
     */
    public function getTraits()
    {
        return $this->traits;
    }

    public function getParentClass()
    {
        return $this->node->extends ? '\\'.(string) $this->node->extends : '';
    }

    /**
     * BC Break: used to be getParentInterfaces
     *
     * @return string[] Names of interfaces the class implements.
     */
    public function getInterfaces()
    {
        $names = array();
        if ($this->node->implements) {
            /** @var PHPParser_Node_Name */
            foreach ($this->node->implements as $node) {
                $names[] = '\\'.(string) $node;
            }
        }

        return $names;
    }
}
