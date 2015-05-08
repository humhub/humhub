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
 * Class for generate Interleaved 2 of 5 barcode
 */
class Code25 extends AbstractObject
{
    /**
     * Coding map
     * - 0 = narrow bar
     * - 1 = wide bar
     * @var array
     */
    protected $codingMap = array(
        '0' => '00110',
        '1' => '10001',
        '2' => '01001',
        '3' => '11000',
        '4' => '00101',
        '5' => '10100',
        '6' => '01100',
        '7' => '00011',
        '8' => '10010',
        '9' => '01010',
    );

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (2 * $this->barThickWidth + 4 * $this->barThinWidth) * $this->factor;
        $characterLength = (3 * $this->barThinWidth + 2 * $this->barThickWidth + 5 * $this->barThinWidth)
                         * $this->factor;
        $encodedData     = strlen($this->getText()) * $characterLength;
        $stopCharacter   = (2 * $this->barThickWidth + 4 * $this->barThinWidth) * $this->factor;
        return $quietZone + $startCharacter + $encodedData + $stopCharacter + $quietZone;
    }

    /**
     * Partial check of interleaved 2 of 5 barcode
     * @return void
     */
    protected function checkSpecificParams()
    {
        $this->checkRatio();
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function prepareBarcode()
    {
        $barcodeTable = array();

        // Start character (30301)
        $barcodeTable[] = array(1, $this->barThickWidth, 0, 1);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->barThickWidth, 0, 1);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(0, $this->barThinWidth);

        $text = str_split($this->getText());
        foreach ($text as $char) {
            $barcodeChar = str_split($this->codingMap[$char]);
            foreach ($barcodeChar as $c) {
                /* visible, width, top, length */
                $width = $c ? $this->barThickWidth : $this->barThinWidth;
                $barcodeTable[] = array(1, $width, 0, 1);
                $barcodeTable[] = array(0, $this->barThinWidth);
            }
        }

        // Stop character (30103)
        $barcodeTable[] = array(1, $this->barThickWidth, 0, 1);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->barThickWidth, 0, 1);
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
        $factor   = 3;
        $checksum = 0;

        for ($i = strlen($text); $i > 0; $i --) {
            $checksum += intval($text{$i - 1}) * $factor;
            $factor    = 4 - $factor;
        }

        $checksum = (10 - ($checksum % 10)) % 10;

        return $checksum;
    }
}
