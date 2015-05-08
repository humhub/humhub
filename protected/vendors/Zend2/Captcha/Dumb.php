<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Captcha;

/**
 * Example dumb word-based captcha
 *
 * Note that only rendering is necessary for word-based captcha
 *
 * @todo       This likely needs its own validation since it expects the word entered to be the strrev of the word stored.
*/
class Dumb extends AbstractWord
{
    /**
     * CAPTCHA label
     * @type string
     */
    protected $label = 'Please type this word backwards';

    /**
     * Set the label for the CAPTCHA
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Retrieve the label for the CAPTCHA
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Retrieve optional view helper name to use when rendering this captcha
     *
     * @return string
     */
    public function getHelperName()
    {
        return 'captcha/dumb';
    }
}
