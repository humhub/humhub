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
 * Class for generate Ean5 barcode
 */
class Ean5 extends Ean13
{

    protected $parities = array(
        0 => array('B','B','A','A','A'),
        1 => array('B','A','B','A','A'),
        2 => array('B','A','A','B','A'),
        3 => array('B','A','A','A','B'),
        4 => array('A','B','B','A','A'),
        5 => array('A','A','B','B','A'),
        6 => array('A','A','A','B','B'),
        7 => array('A','B','A','B','A'),
        8 => array('A','B','A','A','B'),
        9 => array('A','A','B','A','B')
    );

    /**
     * Default options for Ean5 barcode
     * @return void
     */
    protected function getDefaultOptions()
    {
        $this->barcodeLength = 5;
    }

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (5 * $this->barThinWidth) * $this->factor;
        $middleCharacter = (2 * $this->barThinWidth) * $this->factor;
        $encodedData     = (7 * $this->barThinWidth) * $this->factor;
        return $quietZone + $startCharacter + ($this->barcodeLength - 1) * $middleCharacter + $this->barcodeLength * $encodedData + $quietZone;
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function prepareBarcode()
    {
        $barcodeTable = array();

        // Start character (01011)
        $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);

        $firstCharacter = true;
        $textTable = str_split($this->getText());

        // Characters
        for ($i = 0; $i < $this->barcodeLength; $i++) {
            if ($firstCharacter) {
                $firstCharacter = false;
            } else {
                // Intermediate character (01)
                $barcodeTable[] = array(0, $this->barThinWidth, 0, 1);
                $barcodeTable[] = array(1, $this->barThinWidth, 0, 1);
            }
            $bars = str_split($this->codingMap[$this->getParity($i)][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b, $this->barThinWidth, 0, 1);
            }
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
        $checksum = 0;

        for ($i = 0; $i < $this->barcodeLength; $i ++) {
            $checksum += intval($text{$i}) * ($i % 2 ? 9 : 3);
        }

        return ($checksum % 10);
    }

    protected function getParity($i)
    {
        $checksum = $this->getChecksum($this->getText());
        return $this->parities[$checksum][$i];
    }

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getText()
    {
        return $this->addLeadingZeros($this->text);
    }
}
