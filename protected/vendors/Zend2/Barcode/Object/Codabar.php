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
 * Class for generate Codabar barcode
 */
class Codabar extends AbstractObject
{
    /**
     * Coding map
     * - 0 = space
     * - 1 = bar
     * @var array
     */
    protected $codingMap = array(
        '0' => "101010011",     '1' => "101011001",     '2' => "101001011",
        '3' => "110010101",     '4' => "101101001",     '5' => "110101001",
        '6' => "100101011",     '7' => "100101101",     '8' => "100110101",
        '9' => "110100101",     '-' => "101001101",     '$' => "101100101",
        ':' => "1101011011",    '/' => "1101101011",    '.' => "1101101101",
        '+' => "1011011011",    'A' => "1011001001",    'B' => "1010010011",
        'C' => "1001001011",    'D' => "1010011001"
    );

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $encodedData = 0;
        $barcodeChar = str_split($this->getText());
        if (count($barcodeChar) > 1) {
            foreach ($barcodeChar as $c) {
                $encodedData += ((strlen($this->codingMap[$c]) + 1) * $this->barThinWidth) * $this->factor;
            }
        }
        $encodedData -= (1 * $this->barThinWidth * $this->factor);
        return $quietZone + $encodedData + $quietZone;
    }

    /**
     * Partial check of Codabar barcode
     * @return void
     */
    protected function checkSpecificParams()
    {}

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function prepareBarcode()
    {
        $text = str_split($this->getText());
        foreach ($text as $char) {
            $barcodeChar = str_split($this->codingMap[$char]);
            foreach ($barcodeChar as $c) {
                // visible, width, top, length
                $barcodeTable[] = array($c, $this->barThinWidth, 0, 1);
            }
            $barcodeTable[] = array(0, $this->barThinWidth);
        }
        return $barcodeTable;
    }
}
