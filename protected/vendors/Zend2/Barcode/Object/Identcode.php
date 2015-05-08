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
class Identcode extends Code25interleaved
{

    /**
     * Default options for Identcode barcode
     * @return void
     */
    protected function getDefaultOptions()
    {
        $this->barcodeLength = 12;
        $this->mandatoryChecksum = true;
    }

    /**
     * Retrieve text to display
     * @return string
     */
    public function getTextToDisplay()
    {
        return preg_replace('/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{3})([0-9])/',
                            '$1.$2 $3.$4 $5',
                            $this->getText());
    }

    /**
     * Check allowed characters
     * @param  string $value
     * @return string
     * @throws  Exception
     */
    public function validateText($value)
    {
        $this->validateSpecificText($value, array('validator' => $this->getType()));
    }

    /**
     * Get barcode checksum
     *
     * @param  string $text
     * @return int
     */
    public function getChecksum($text)
    {
        $this->checkText($text);
        $checksum = 0;

        for ($i = strlen($text); $i > 0; $i --) {
            $checksum += intval($text{$i - 1}) * (($i % 2) ? 4 : 9);
        }

        $checksum = (10 - ($checksum % 10)) % 10;

        return $checksum;
    }
}
