<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\File\Transfer\Adapter;

use Zend\Filter\FilterPluginManager as BaseManager;

/**
 * Plugin manager implementation for the filter chain.
 *
 * Enforces that filters retrieved are instances of
 * FilterInterface. Additionally, it registers a number of default filters.
 *
 */
class FilterPluginManager extends BaseManager
{
    /**
     * Default set of filters
     *
     * @var array
     */
    protected $aliases = array(
        'decrypt'   => 'filedecrypt',
        'encrypt'   => 'fileencrypt',
        'lowercase' => 'filelowercase',
        'rename'    => 'filerename',
        'uppercase' => 'fileuppercase',
    );
}
