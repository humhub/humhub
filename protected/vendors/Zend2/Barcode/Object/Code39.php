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
 * Class for generate Code39 barcode
 */
class Code39 extends AbstractObject
{
    /**
     * Coding map
     * @var array
     */
    protected $codingMap = array(
        '0' => '000110100',
        '1' => '100100001',
        '2' => '001100001',
        '3' => '101100000',
        '4' => '000110001',
        '5' => '100110000',
        '6' => '001110000',
        '7' => '000100101',
        '8' => '100100100',
        '9' => '001100100',
        'A' => '100001001',
        'B' => '001001001',
        'C' => '101001000',
        'D' => '000011001',
        'E' => '100011000',
        'F' => '001011000',
        'G' => '000001101',
        'H' => '100001100',
        'I' => '001001100',
        'J' => '000011100',
        'K' => '100000011',
        'L' => '001000011',
        'M' => '101000010',
        'N' => '000010011',
        'O' => '100010010',
        'P' => '001010010',
        'Q' => '000000111',
        'R' => '100000110',
        'S' => '001000110',
        'T' => '000010110',
        'U' => '110000001',
        'V' => '011000001',
        'W' => '111000000',
        'X' => '010010001',
        'Y' => '110010000',
        'Z' => '011010000',
        '-' => '010000101',
        '.' => '110000100',
        ' ' => '011000100',
        '$' => '010101000',
        '/' => '010100010',
        '+' => '010001010',
        '%' => '000101010',
        '*' => '010010100',
    );

    /**
     * Partial check of Code39 barcode
     * @return void
     */
    protected function checkSpecificParams()
    {
        $this->checkRatio();
    }

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $characterLength = (6 * $this->barThinWidth + 3 * $this->barThickWidth + 1) * $this->factor;
        $encodedData     = strlen($this->getText()) * $characterLength - $this->factor;
        return $quietZone + $encodedData + $quietZone;
    }

    /**
     * Set text to encode
     * @param string $value
     * @return Code39
     */
    public function setText($value)
    {
        $this->text = $value;
        return $this;
    }

    /**
     * Retrieve text to display
     * @return string
     */
    public function getText()
    {
        return '*' . parent::getText() . '*';
    }

    /**
     * Retrieve text to display
     * @return string
     */
    public function getTextToDisplay()
    {
        $text = parent::getTextToDisplay();
        if (substr($text, 0, 1) != '*' && substr($text, -1) != '*') {
            return '*' . $text . '*';
        }

        return $text;
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function prepareBarcode()
    {
        $text         = str_split($this->getText());
        $barcodeTable = array();
        foreach ($text as $char) {
            $barcodeChar = str_split($this->codingMap[$char]);
            $visible     = true;
            foreach ($barcodeChar as $c) {
                /* visible, width, top, length */
                $width          = $c ? $this->barThickWidth : $this->barThinWidth;
                $barcodeTable[] = array((int) $visible, $width, 0, 1);
                $visible = ! $visible;
            }
            $barcodeTable[] = array(0, $this->barThinWidth);
        }
        return $barcodeTable;
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
        $text     = str_split($text);
        $charset  = array_flip(array_keys($this->codingMap));
        $checksum = 0;
        foreach ($text as $character) {
            $checksum += $charset[$character];
        }
        return array_search(($checksum % 43), $charset);
    }
}
