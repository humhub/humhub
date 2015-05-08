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
 * Class for generate Ean13 barcode
 */
class Ean13 extends AbstractObject
{

    /**
     * Coding map
     * - 0 = narrow bar
     * - 1 = wide bar
     * @var array
     */
    protected $codingMap = array(
        'A' => array(
            0 => "0001101", 1 => "0011001", 2 => "0010011", 3 => "0111101", 4 => "0100011",
            5 => "0110001", 6 => "0101111", 7 => "0111011", 8 => "0110111", 9 => "0001011"
        ),
        'B' => array(
            0 => "0100111", 1 => "0110011", 2 => "0011011", 3 => "0100001", 4 => "0011101",
            5 => "0111001", 6 => "0000101", 7 => "0010001", 8 => "0001001", 9 => "0010111"
        ),
        'C' => array(
            0 => "1110010", 1 => "1100110", 2 => "1101100", 3 => "1000010", 4 => "1011100",
            5 => "1001110", 6 => "1010000", 7 => "1000100", 8 => "1001000", 9 => "1110100"
        ));

    protected $parities = array(
        0 => array('A','A','A','A','A','A'),
        1 => array('A','A','B','A','B','B'),
        2 => array('A','A','B','B','A','B'),
        3 => array('A','A','B','B','B','A'),
        4 => array('A','B','A','A','B','B'),
        5 => array('A','B','B','A','A','B'),
        6 => array('A','B','B','B','A','A'),
        7 => array('A','B','A','B','A','B'),
        8 => array('A','B','A','B','B','A'),
        9 => array('A','B','B','A','B','A')
    );

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function getDefaultOptions()
    {
        $this->barcodeLength = 13;
        $this->mandatoryChecksum = true;
        $this->mandatoryQuietZones = true;
    }

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (3 * $this->barThinWidth) * $this->factor;
        $middleCharacter = (5 * $this->barThinWidth) * $this->factor;
        $stopCharacter   = (3 * $this->barThinWidth) * $this->factor;
        $encodedData     = (7 * $this->barThinWidth) * $this->factor * 12;
        return $quietZone + $startCharacter + $middleCharacter + $encodedData + $stopCharacter + $quietZone;
    }

    /**
     * Partial check of interleaved EAN/UPC barcode
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
        $height = ($this->drawText) ? 1.1 : 1;

        // Start character (101)
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);

        $textTable = str_split($this->getText());
        $parity = $this->parities[$textTable[0]];

        // First part
        for ($i = 1; $i < 7; $i++) {
            $bars = str_split($this->codingMap[$parity[$i - 1]][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b, $this->barThinWidth, 0, 1);
            }
        }

        // Middle character (01010)
        $barcodeTable[] = array(0, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, $height);

        // Second part
        for ($i = 7; $i < 13; $i++) {
            $bars = str_split($this->codingMap['C'][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b, $this->barThinWidth, 0, 1);
            }
        }

        // Stop character (101)
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);
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

    /**
     * Partial function to draw text
     * @return void
     */
    protected function drawText()
    {
        if (get_class($this) == 'Zend\Barcode\Object\Ean13') {
            $this->drawEan13Text();
        } else {
            parent::drawText();
        }
    }

    protected function drawEan13Text()
    {
        if ($this->drawText) {
            $text = $this->getTextToDisplay();
            $characterWidth = (7 * $this->barThinWidth) * $this->factor;
            $leftPosition = $this->getQuietZone() - $characterWidth;
            for ($i = 0; $i < $this->barcodeLength; $i ++) {
                $this->addText(
                    $text{$i},
                    $this->fontSize * $this->factor,
                    $this->rotate(
                        $leftPosition,
                        (int) $this->withBorder * 2
                            + $this->factor * ($this->barHeight + $this->fontSize) + 1
                    ),
                    $this->font,
                    $this->foreColor,
                    'left',
                    - $this->orientation
                );
                switch ($i) {
                    case 0:
                        $factor = 3;
                        break;
                    case 6:
                        $factor = 4;
                        break;
                    default:
                        $factor = 0;
                }
                $leftPosition = $leftPosition + $characterWidth + ($factor * $this->barThinWidth * $this->factor);
            }
        }
    }
}
