<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator\ScrollingStyle;

use Zend\Paginator\Paginator;

/**
 * A scrolling style that returns every page in the collection.
 * Useful when it is necessary to make every page available at
 * once--for example, when using a drop-down menu pagination control.
 */
class All implements ScrollingStyleInterface
{
    /**
     * Returns an array of all pages given a page number and range.
     *
     * @param  Paginator $paginator
     * @param  int $pageRange Unused
     * @return array
     */
    public function getPages(Paginator $paginator, $pageRange = null)
    {
        return $paginator->getPagesInRange(1, $paginator->count());
    }
}
