<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Barcode\Object;

/**
 * Class for generate Identcode barcode
 */
class Leitcode extends Identcode
{

    /**
     * Default options for Leitcode barcode
     * @return void
     */
    protected function getDefaultOptions()
    {
        $this->barcodeLength = 14;
        $this->mandatoryChecksum = true;
    }

    /**
     * Retrieve text to display
     * @return string
     */
    public function getTextToDisplay()
    {
        return preg_replace('/([0-9]{5})([0-9]{3})([0-9]{3})([0-9]{2})([0-9])/',
                            '$1.$2.$3.$4 $5',
                            $this->getText());
    }
}
