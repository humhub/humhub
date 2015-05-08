<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Captcha;

use Zend\Text\Figlet\Figlet as FigletManager;

/**
 * Captcha based on figlet text rendering service
 *
 * Note that this engine seems not to like numbers
 */
class Figlet extends AbstractWord
{
    /**
     * Figlet text renderer
     *
     * @var FigletManager
     */
    protected $figlet;

    /**
     * Constructor
     *
     * @param  null|string|array|\Traversable $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->figlet = new FigletManager($options);
    }

    /**
     * Retrieve the composed figlet manager
     *
     * @return FigletManager
     */
    public function getFiglet()
    {
        return $this->figlet;
    }

    /**
     * Generate new captcha
     *
     * @return string
     */
    public function generate()
    {
        $this->useNumbers = false;
        return parent::generate();
    }

    /**
     * Get helper name used to render captcha
     *
     * @return string
     */
    public function getHelperName()
    {
        return 'captcha/figlet';
    }
}
