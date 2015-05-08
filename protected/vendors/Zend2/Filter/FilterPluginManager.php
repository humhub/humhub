<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for the filter chain.
 *
 * Enforces that filters retrieved are either callbacks or instances of
 * FilterInterface. Additionally, it registers a number of default filters
 * available, as well as aliases for them.
 */
class FilterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of filters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'alnum'                     => 'Zend\I18n\Filter\Alnum',
        'alpha'                     => 'Zend\I18n\Filter\Alpha',
        'basename'                  => 'Zend\Filter\BaseName',
        'boolean'                   => 'Zend\Filter\Boolean',
        'callback'                  => 'Zend\Filter\Callback',
        'compress'                  => 'Zend\Filter\Compress',
        'compressbz2'               => 'Zend\Filter\Compress\Bz2',
        'compressgz'                => 'Zend\Filter\Compress\Gz',
        'compresslzf'               => 'Zend\Filter\Compress\Lzf',
        'compressrar'               => 'Zend\Filter\Compress\Rar',
        'compresssnappy'            => 'Zend\Filter\Compress\Snappy',
        'compresstar'               => 'Zend\Filter\Compress\Tar',
        'compresszip'               => 'Zend\Filter\Compress\Zip',
        'datetimeformatter'         => 'Zend\Filter\DateTimeFormatter',
        'decompress'                => 'Zend\Filter\Decompress',
        'decrypt'                   => 'Zend\Filter\Decrypt',
        'digits'                    => 'Zend\Filter\Digits',
        'dir'                       => 'Zend\Filter\Dir',
        'encrypt'                   => 'Zend\Filter\Encrypt',
        'encryptblockcipher'        => 'Zend\Filter\Encrypt\BlockCipher',
        'encryptopenssl'            => 'Zend\Filter\Encrypt\Openssl',
        'filedecrypt'               => 'Zend\Filter\File\Decrypt',
        'fileencrypt'               => 'Zend\Filter\File\Encrypt',
        'filelowercase'             => 'Zend\Filter\File\LowerCase',
        'filerename'                => 'Zend\Filter\File\Rename',
        'filerenameupload'          => 'Zend\Filter\File\RenameUpload',
        'fileuppercase'             => 'Zend\Filter\File\UpperCase',
        'htmlentities'              => 'Zend\Filter\HtmlEntities',
        'inflector'                 => 'Zend\Filter\Inflector',
        'int'                       => 'Zend\Filter\Int',
        'null'                      => 'Zend\Filter\Null',
        'numberformat'              => 'Zend\I18n\Filter\NumberFormat',
        'pregreplace'               => 'Zend\Filter\PregReplace',
        'realpath'                  => 'Zend\Filter\RealPath',
        'stringtolower'             => 'Zend\Filter\StringToLower',
        'stringtoupper'             => 'Zend\Filter\StringToUpper',
        'stringtrim'                => 'Zend\Filter\StringTrim',
        'stripnewlines'             => 'Zend\Filter\StripNewlines',
        'striptags'                 => 'Zend\Filter\StripTags',
        'urinormalize'              => 'Zend\Filter\UriNormalize',
        'wordcamelcasetodash'       => 'Zend\Filter\Word\CamelCaseToDash',
        'wordcamelcasetoseparator'  => 'Zend\Filter\Word\CamelCaseToSeparator',
        'wordcamelcasetounderscore' => 'Zend\Filter\Word\CamelCaseToUnderscore',
        'worddashtocamelcase'       => 'Zend\Filter\Word\DashToCamelCase',
        'worddashtoseparator'       => 'Zend\Filter\Word\DashToSeparator',
        'worddashtounderscore'      => 'Zend\Filter\Word\DashToUnderscore',
        'wordseparatortocamelcase'  => 'Zend\Filter\Word\SeparatorToCamelCase',
        'wordseparatortodash'       => 'Zend\Filter\Word\SeparatorToDash',
        'wordseparatortoseparator'  => 'Zend\Filter\Word\SeparatorToSeparator',
        'wordunderscoretocamelcase' => 'Zend\Filter\Word\UnderscoreToCamelCase',
        'wordunderscoretodash'      => 'Zend\Filter\Word\UnderscoreToDash',
        'wordunderscoretoseparator' => 'Zend\Filter\Word\UnderscoreToSeparator',
    );

    /**
     * Whether or not to share by default; default to false
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof FilterInterface) {
            // we're okay
            return;
        }
        if (is_callable($plugin)) {
            // also okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\FilterInterface or be callable',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
