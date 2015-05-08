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
 * Class for generate Postnet barcode
 */
class Postnet extends AbstractObject
{

    /**
     * Coding map
     * - 0 = half bar
     * - 1 = complete bar
     * @var array
     */
    protected $codingMap = array(
        0 => "11000",
        1 => "00011",
        2 => "00101",
        3 => "00110",
        4 => "01001",
        5 => "01010",
        6 => "01100",
        7 => "10001",
        8 => "10010",
        9 => "10100"
    );

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function getDefaultOptions()
    {
        $this->barThinWidth = 2;
        $this->barHeight = 20;
        $this->drawText = false;
        $this->stretchText = true;
        $this->mandatoryChecksum = true;
    }

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (2 * $this->barThinWidth) * $this->factor;
        $stopCharacter   = (1 * $this->barThinWidth) * $this->factor;
        $encodedData     = (10 * $this->barThinWidth) * $this->factor * strlen($this->getText());
        return $quietZone + $startCharacter + $encodedData + $stopCharacter + $quietZone;
    }

    /**
     * Partial check of interleaved Postnet barcode
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
        $barcodeTable = array();

        // Start character (1)
        $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);

        // Text to encode
        $textTable = str_split($this->getText());
        foreach ($textTable as $char) {
            $bars = str_split($this->codingMap[$char]);
            foreach ($bars as $b) {
                $barcodeTable[] = array(1, $this->barThinWidth, 0.5 - $b * 0.5, 1);
                $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
            }
        }

        // Stop character (1)
        $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);
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
        $sum = array_sum(str_split($text));
        $checksum = (10 - ($sum % 10)) % 10;
        return $checksum;
    }
}
