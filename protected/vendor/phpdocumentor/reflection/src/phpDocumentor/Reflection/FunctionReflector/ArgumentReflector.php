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

namespace phpDocumentor\Reflection\FunctionReflector;

use phpDocumentor\Reflection\BaseReflector;

class ArgumentReflector extends BaseReflector
{
    /** @var \PHPParser_Node_Param */
    protected $node;

    /**
     * Checks whether the argument is passed by reference.
     *
     * @return bool TRUE if the argument is by reference, FALSE otherwise.
     */
    public function isByRef()
    {
        return $this->node->byRef;
    }

    /**
     * Returns the default value or null is none is set.
     *
     * @return string|null
     */
    public function getDefault()
    {
        $result = null;
        if ($this->node->default) {
            $result = $this->getRepresentationOfValue($this->node->default);
        }

        return $result;
    }

    /**
     * Returns the typehint, or null if none is set.
     *
     * @return string|null
     */
    public function getType()
    {
        $type = (string) $this->node->type;

        // in case of the callable of array keyword; do not prefix with a \
        if ($type == 'callable' || $type == 'array'
            || $type == 'self' || $type == '$this'
        ) {
            return $type;
        }

        return $type ? '\\'.$type : '';
    }

    public function getName()
    {
        return '$'.parent::getName();
    }
}
