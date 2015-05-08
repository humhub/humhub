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
 * Class for generate UpcA barcode
 */
class Upca extends Ean13
{

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function getDefaultOptions()
    {
        $this->barcodeLength = 12;
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

        // First character
        $bars = str_split($this->codingMap['A'][$textTable[0]]);
        foreach ($bars as $b) {
            $barcodeTable[] = array($b, $this->barThinWidth, 0, $height);
        }

        // First part
        for ($i = 1; $i < 6; $i++) {
            $bars = str_split($this->codingMap['A'][$textTable[$i]]);
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
        for ($i = 6; $i < 11; $i++) {
            $bars = str_split($this->codingMap['C'][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b, $this->barThinWidth, 0, 1);
            }
        }

        // Last character
        $bars = str_split($this->codingMap['C'][$textTable[11]]);
        foreach ($bars as $b) {
            $barcodeTable[] = array($b, $this->barThinWidth, 0, $height);
        }

        // Stop character (101)
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(0, $this->barThinWidth, 0, $height);
        $barcodeTable[] = array(1, $this->barThinWidth, 0, $height);
        return $barcodeTable;
    }

    /**
     * Partial function to draw text
     * @return void
     */
    protected function drawText()
    {
        if ($this->drawText) {
            $text = $this->getTextToDisplay();
            $characterWidth = (7 * $this->barThinWidth) * $this->factor;
            $leftPosition = $this->getQuietZone() - $characterWidth;
            for ($i = 0; $i < $this->barcodeLength; $i ++) {
                $fontSize = $this->fontSize;
                if ($i == 0 || $i == 11) {
                    $fontSize *= 0.8;
                }
                $this->addText(
                    $text{$i},
                    $fontSize * $this->factor,
                    $this->rotate(
                        $leftPosition,
                        (int) $this->withBorder * 2
                            + $this->factor * ($this->barHeight + $fontSize) + 1
                    ),
                    $this->font,
                    $this->foreColor,
                    'left',
                    - $this->orientation
                );
                switch ($i) {
                    case 0:
                        $factor = 10;
                        break;
                    case 5:
                        $factor = 4;
                        break;
                    case 10:
                        $factor = 11;
                        break;
                    default:
                        $factor = 0;
                }
                $leftPosition = $leftPosition + $characterWidth + ($factor * $this->barThinWidth * $this->factor);
            }
        }
    }
}
