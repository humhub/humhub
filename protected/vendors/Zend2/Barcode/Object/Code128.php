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
 * Class for generate Code128 barcode
 */
class Code128 extends AbstractObject
{
    /**
     * Drawing of checksum
     * (even if it's sometime optional, most of time it's required)
     * @var bool
     */
    protected $withChecksum = true;

    /**
     * @var array
     */
    protected $convertedText = array();

    protected $codingMap = array(
                 0 => "11011001100",   1 => "11001101100",   2 => "11001100110",
                 3 => "10010011000",   4 => "10010001100",   5 => "10001001100",
                 6 => "10011001000",   7 => "10011000100",   8 => "10001100100",
                 9 => "11001001000",  10 => "11001000100",  11 => "11000100100",
                12 => "10110011100",  13 => "10011011100",  14 => "10011001110",
                15 => "10111001100",  16 => "10011101100",  17 => "10011100110",
                18 => "11001110010",  19 => "11001011100",  20 => "11001001110",
                21 => "11011100100",  22 => "11001110100",  23 => "11101101110",
                24 => "11101001100",  25 => "11100101100",  26 => "11100100110",
                27 => "11101100100",  28 => "11100110100",  29 => "11100110010",
                30 => "11011011000",  31 => "11011000110",  32 => "11000110110",
                33 => "10100011000",  34 => "10001011000",  35 => "10001000110",
                36 => "10110001000",  37 => "10001101000",  38 => "10001100010",
                39 => "11010001000",  40 => "11000101000",  41 => "11000100010",
                42 => "10110111000",  43 => "10110001110",  44 => "10001101110",
                45 => "10111011000",  46 => "10111000110",  47 => "10001110110",
                48 => "11101110110",  49 => "11010001110",  50 => "11000101110",
                51 => "11011101000",  52 => "11011100010",  53 => "11011101110",
                54 => "11101011000",  55 => "11101000110",  56 => "11100010110",
                57 => "11101101000",  58 => "11101100010",  59 => "11100011010",
                60 => "11101111010",  61 => "11001000010",  62 => "11110001010",
                63 => "10100110000",  64 => "10100001100",  65 => "10010110000",
                66 => "10010000110",  67 => "10000101100",  68 => "10000100110",
                69 => "10110010000",  70 => "10110000100",  71 => "10011010000",
                72 => "10011000010",  73 => "10000110100",  74 => "10000110010",
                75 => "11000010010",  76 => "11001010000",  77 => "11110111010",
                78 => "11000010100",  79 => "10001111010",  80 => "10100111100",
                81 => "10010111100",  82 => "10010011110",  83 => "10111100100",
                84 => "10011110100",  85 => "10011110010",  86 => "11110100100",
                87 => "11110010100",  88 => "11110010010",  89 => "11011011110",
                90 => "11011110110",  91 => "11110110110",  92 => "10101111000",
                93 => "10100011110",  94 => "10001011110",  95 => "10111101000",
                96 => "10111100010",  97 => "11110101000",  98 => "11110100010",
                99 => "10111011110", 100 => "10111101110", 101 => "11101011110",
               102 => "11110101110",
               103 => "11010000100", 104 => "11010010000", 105 => "11010011100",
               106 => "1100011101011");

    /**
    * Character sets ABC
    * @var array
    */
    protected $charSets = array(
        'A' => array(
            ' ', '!', '"', '#', '$', '%', '&', "'",
            '(', ')', '*', '+', ',', '-', '.', '/',
            '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', ':', ';', '<', '=', '>', '?',
            '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
            'X', 'Y', 'Z', '[', '\\', ']', '^', '_',
            0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07,
            0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F,
            0x10, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17,
            0x18, 0x19, 0x1A, 0x1B, 0x1C, 0x1D, 0x1E, 0x1F,
            'FNC3', 'FNC2', 'SHIFT', 'Code C', 'Code B', 'FNC4', 'FNC1',
            'START A', 'START B', 'START C', 'STOP'),
        'B' => array(
            ' ', '!', '"', '#', '$', '%', '&', "'",
            '(', ')', '*', '+', ',', '-', '.', '/',
            '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', ':', ';', '<', '=', '>', '?',
            '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
            'X', 'Y', 'Z', '[', '\\', ']', '^', '_',
            '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g',
            'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
            'p', 'q', 'r', 's', 't', 'u', 'v', 'w',
            'x', 'y', 'z', '{', '|', '}', '~', 0x7F,
            'FNC3', 'FNC2', 'SHIFT', 'Code C', 'FNC4', 'Code A', 'FNC1',
            'START A', 'START B', 'START C', 'STOP',),
        'C' => array(
            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
            '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '20', '21', '22', '23', '24', '25', '26', '27', '28', '29',
            '30', '31', '32', '33', '34', '35', '36', '37', '38', '39',
            '40', '41', '42', '43', '44', '45', '46', '47', '48', '49',
            '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
            '60', '61', '62', '63', '64', '65', '66', '67', '68', '69',
            '70', '71', '72', '73', '74', '75', '76', '77', '78', '79',
            '80', '81', '82', '83', '84', '85', '86', '87', '88', '89',
            '90', '91', '92', '93', '94', '95', '96', '97', '98', '99',
            'Code B', 'Code A', 'FNC1', 'START A', 'START B', 'START C', 'STOP'));

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function calculateBarcodeWidth()
    {
        $quietZone = $this->getQuietZone();
        // Each characters contain 11 bars...
        $characterLength = 11 * $this->barThinWidth * $this->factor;
        $convertedChars = count($this->convertToBarcodeChars($this->getText()));
        if ($this->withChecksum) {
            $convertedChars++;
        }
        $encodedData = $convertedChars * $characterLength;
        // ...except the STOP character (13)
        $encodedData += $characterLength + 2 * $this->barThinWidth * $this->factor;
        $width = $quietZone + $encodedData + $quietZone;
        return $width;
    }

    /**
     * Partial check of code128 barcode
     * @return void
     */
    protected function checkSpecificParams()
    {
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function prepareBarcode()
    {
        $barcodeTable = array();

        $convertedChars = $this->convertToBarcodeChars($this->getText());

        if ($this->withChecksum) {
            $convertedChars[] = $this->getChecksum($this->getText());
        }

        // STOP CHARACTER
        $convertedChars[] = 106;

        foreach ($convertedChars as $barcodeChar) {
            $barcodePattern = $this->codingMap[$barcodeChar];
            foreach (str_split($barcodePattern) as $c) {
                $barcodeTable[] = array($c, $this->barThinWidth, 0, 1);
            }
        }
        return $barcodeTable;
    }

    /**
     * Checks if the next $length chars of $string starting at $pos are numeric.
     * Returns false if the end of the string is reached.
     * @param string $string String to search
     * @param int    $pos Starting position
     * @param int    $length Length to search
     * @return bool
     */
    protected static function _isDigit($string, $pos, $length = 2)
    {
        if ($pos + $length > strlen($string)) {
           return false;
        }

        for ($i = $pos; $i < $pos + $length; $i++) {
              if (!is_numeric($string[$i])) {
                  return false;
              }
        }
        return true;
    }

    /**
     * Convert string to barcode string
     * @param string $string
     * @return array
     */
    protected function convertToBarcodeChars($string)
    {
        $string = (string) $string;
        if (!strlen($string)) {
            return array();
        }

        if (isset($this->convertedText[md5($string)])) {
            return $this->convertedText[md5($string)];
        }

        $currentCharset = null;
        $result = array();

        for ($pos = 0; $pos < strlen($string); $pos++) {
            $char = $string[$pos];
            $code = null;

            if (static::_isDigit($string, $pos, 4) && $currentCharset != 'C'
             || static::_isDigit($string, $pos, 2) && $currentCharset == 'C') {
                /**
                 * Switch to C if the next 4 chars are numeric or stay C if the next 2
                 * chars are numeric
                 */
                if ($currentCharset != 'C') {
                    if ($pos == 0) {
                        $code = array_search("START C", $this->charSets['C']);
                    } else {
                        $code = array_search("Code C", $this->charSets[$currentCharset]);
                    }
                    $result[] = $code;
                    $currentCharset = 'C';
                }
            } elseif (in_array($char, $this->charSets['B']) && $currentCharset != 'B'
                  && !(in_array($char, $this->charSets['A']) && $currentCharset == 'A')) {
                /**
                 * Switch to B as B contains the char and B is not the current charset.
                 */
                if ($pos == 0) {
                    $code = array_search("START B", $this->charSets['B']);
                } else {
                    $code = array_search("Code B", $this->charSets[$currentCharset]);
                }
                $result[] = $code;
                $currentCharset = 'B';
            } elseif (array_key_exists($char, $this->charSets['A']) && $currentCharset != 'A'
                  && !(array_key_exists($char, $this->charSets['B']) && $currentCharset == 'B')) {
                /**
                 * Switch to C as C contains the char and C is not the current charset.
                 */
                if ($pos == 0) {
                    $code = array_search("START A", $this->charSets['A']);
                } else {
                    $code =array_search("Code A", $this->charSets[$currentCharset]);
                }
                $result[] = $code;
                $currentCharset = 'A';
            }

            if ($currentCharset == 'C') {
                $code = array_search(substr($string, $pos, 2), $this->charSets['C']);
                $pos++; //Two chars from input
            } else {
                $code = array_search($string[$pos], $this->charSets[$currentCharset]);
            }
            $result[] = $code;
        }

        $this->convertedText[md5($string)] = $result;
        return $result;
    }

    /**
     * Set text to encode
     * @param string $value
     * @return Code128
     */
    public function setText($value)
    {
        $this->text = $value;
        return $this;
    }

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get barcode checksum
     *
     * @param  string $text
     * @return int
     */
    public function getChecksum($text)
    {
        $tableOfChars = $this->convertToBarcodeChars($text);

        $sum = $tableOfChars[0];
        unset($tableOfChars[0]);

        $k = 1;
        foreach ($tableOfChars as $char) {
            $sum += ($k++) * $char;
        }

        $checksum = $sum % 103;

        return $checksum;
    }
}
