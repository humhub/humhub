<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Barcode\Object;

use Zend\Validator\Barcode as BarcodeValidator;

/**
 * Class for generate Ean8 barcode
 */
class Ean8 extends Ean13
{

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function getDefaultOptions()
    {
        $this->barcodeLength = 8;
        $this->mandatoryChecksum = true;
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
        $encodedData     = (7 * $this->barThinWidth) * $this->factor * 8;
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

        // First part
        for ($i = 0; $i < 4; $i++) {
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
        for ($i = 4; $i < 8; $i++) {
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
     * Partial function to draw text
     * @return void
     */
    protected function drawText()
    {
        if ($this->drawText) {
            $text = $this->getTextToDisplay();
            $characterWidth = (7 * $this->barThinWidth) * $this->factor;
            $leftPosition = $this->getQuietZone() + (3 * $this->barThinWidth) * $this->factor;
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
                    case 3:
                        $factor = 4;
                        break;
                    default:
                        $factor = 0;
                }
                $leftPosition = $leftPosition + $characterWidth + ($factor * $this->barThinWidth * $this->factor);
            }
        }
    }

    /**
     * Particular validation for Ean8 barcode objects
     * (to suppress checksum character substitution)
     *
     * @param string $value
     * @param array  $options
     * @throws Exception\BarcodeValidationException
     */
    protected function validateSpecificText($value, $options = array())
    {
        $validator = new BarcodeValidator(array(
            'adapter'  => 'ean8',
            'checksum' => false,
        ));

        $value = $this->addLeadingZeros($value, true);

        if (!$validator->isValid($value)) {
            $message = implode("\n", $validator->getMessages());
            throw new Exception\BarcodeValidationException($message);
        }
    }
}
