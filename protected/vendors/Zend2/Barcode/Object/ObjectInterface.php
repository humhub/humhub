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
 * Interface for generate Barcode
 */
interface ObjectInterface
{
    /**
     * Constructor
     * @param array|\Traversable $options
     */
    public function __construct($options = null);

    /**
     * Set barcode state from options array
     * @param  array $options
     * @return ObjectInterface
     */
    public function setOptions($options);

    /**
     * Set barcode namespace for autoloading
     *
     * @param string $namespace
     * @return ObjectInterface
     */
    public function setBarcodeNamespace($namespace);

    /**
     * Retrieve barcode namespace
     *
     * @return string
     */
    public function getBarcodeNamespace();

    /**
     * Retrieve type of barcode
     * @return string
     */
    public function getType();

    /**
     * Set height of the barcode bar
     * @param int $value
     * @return ObjectInterface
     */
    public function setBarHeight($value);

    /**
     * Get height of the barcode bar
     * @return int
     */
    public function getBarHeight();

    /**
     * Set thickness of thin bar
     * @param int $value
     * @return ObjectInterface
     */
    public function setBarThinWidth($value);

    /**
     * Get thickness of thin bar
     * @return int
     */
    public function getBarThinWidth();

    /**
     * Set thickness of thick bar
     * @param int $value
     * @return ObjectInterface
     */
    public function setBarThickWidth($value);

    /**
     * Get thickness of thick bar
     * @return int
     */
    public function getBarThickWidth();

    /**
     * Set factor applying to
     * thinBarWidth - thickBarWidth - barHeight - fontSize
     * @param int $value
     * @return ObjectInterface
     */
    public function setFactor($value);

    /**
     * Get factor applying to
     * thinBarWidth - thickBarWidth - barHeight - fontSize
     * @return int
     */
    public function getFactor();

    /**
     * Set color of the barcode and text
     * @param string $value
     * @return ObjectInterface
     */
    public function setForeColor($value);

    /**
     * Retrieve color of the barcode and text
     * @return int
     */
    public function getForeColor();

    /**
     * Set the color of the background
     * @param int $value
     * @return ObjectInterface
     */
    public function setBackgroundColor($value);

    /**
     * Retrieve background color of the image
     * @return int
     */
    public function getBackgroundColor();

    /**
     * Activate/deactivate drawing of the bar
     * @param  bool $value
     * @return ObjectInterface
     */
    public function setWithBorder($value);

    /**
     * Retrieve if border are draw or not
     * @return bool
     */
    public function getWithBorder();

    /**
     * Allow fast inversion of font/bars color and background color
     * @return ObjectInterface
     */
    public function setReverseColor();

    /**
     * Set orientation of barcode and text
     * @param float $value
     * @return ObjectInterface
     */
    public function setOrientation($value);

    /**
     * Retrieve orientation of barcode and text
     * @return float
     */
    public function getOrientation();

    /**
     * Set text to encode
     * @param string $value
     * @return ObjectInterface
     */
    public function setText($value);

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getText();

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getRawText();

    /**
     * Retrieve text to display
     * @return string
     */
    public function getTextToDisplay();

    /**
     * Activate/deactivate drawing of text to encode
     * @param  bool $value
     * @return ObjectInterface
     */
    public function setDrawText($value);

    /**
     * Retrieve if drawing of text to encode is enabled
     * @return bool
     */
    public function getDrawText();

    /**
     * Activate/deactivate the adjustment of the position
     * of the characters to the position of the bars
     * @param  bool $value
     * @return ObjectInterface
     */
    public function setStretchText($value);

    /**
     * Retrieve if the adjustment of the position of the characters
     * to the position of the bars is enabled
     * @return bool
     */
    public function getStretchText();

    /**
     * Activate/deactivate the automatic generation
     * of the checksum character
     * added to the barcode text
     * @param  bool $value
     * @return ObjectInterface
     */
    public function setWithChecksum($value);

    /**
     * Retrieve if the checksum character is automatically
     * added to the barcode text
     * @return bool
     */
    public function getWithChecksum();

    /**
     * Activate/deactivate the automatic generation
     * of the checksum character
     * added to the barcode text
     * @param  bool $value
     * @return ObjectInterface
     */
    public function setWithChecksumInText($value);

    /**
     * Retrieve if the checksum character is automatically
     * added to the barcode text
     * @return bool
     */
    public function getWithChecksumInText();

    /**
     * Set the font:
     *  - if integer between 1 and 5, use gd built-in fonts
     *  - if string, $value is assumed to be the path to a TTF font
     * @param int|string $value
     * @return ObjectInterface
     */
    public function setFont($value);

    /**
     * Retrieve the font
     * @return int|string
     */
    public function getFont();

    /**
     * Set the size of the font in case of TTF
     * @param float $value
     * @return ObjectInterface
     */
    public function setFontSize($value);

    /**
     * Retrieve the size of the font in case of TTF
     * @return float
     */
    public function getFontSize();

    /**
     * Quiet zone before first bar
     * and after the last bar
     * @return int
     */
    public function getQuietZone();

    /**
     * Retrieve the set of drawing instructions
     * @return array
     */
    public function getInstructions();

    /**
     * Checking of parameters after all settings
     * @return void
     */
    public function checkParams();

    /**
     * Get height of the result object
     * @param  bool $recalculate
     * @return int
     */
    public function getHeight($recalculate = false);

    /**
     * Get width of the result object
     * @param  bool $recalculate
     * @return int
     */
    public function getWidth($recalculate = false);

    /**
     * Calculate the offset from the left of the object
     * if an orientation is activated
     * @param  bool $recalculate
     * @return float
     */
    public function getOffsetLeft($recalculate = false);

    /**
     * Calculate the offset from the top of the object
     * if an orientation is activated
     * @param  bool $recalculate
     * @return float
     */
    public function getOffsetTop($recalculate = false);

    /**
     * Complete drawing of the barcode
     * @return array Table of instructions
     */
    public function draw();

    /**
     * Check for invalid characters
     * @param   string $value    Text to be checked
     * @return void
     */
    public function validateText($value);
}
