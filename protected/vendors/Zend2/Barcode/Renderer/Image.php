<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Barcode\Renderer;

use Zend\Barcode\Exception\RendererCreationException;
use Zend\Stdlib\ErrorHandler;

/**
 * Class for rendering the barcode as image
 */
class Image extends AbstractRenderer
{
    /**
     * List of authorized output format
     * @var array
     */
    protected $allowedImageType = array('png',
                                        'jpeg',
                                        'gif'  );

    /**
     * Image format
     * @var string
     */
    protected $imageType = 'png';

    /**
     * Resource for the image
     * @var resource
     */
    protected $resource = null;

    /**
     * Resource for the font and bars color of the image
     * @var int
     */
    protected $imageForeColor = null;

    /**
     * Resource for the background color of the image
     * @var int
     */
    protected $imageBackgroundColor = null;

    /**
     * Height of the rendered image wanted by user
     * @var int
     */
    protected $userHeight = 0;

    /**
     * Width of the rendered image wanted by user
     * @var int
     */
    protected $userWidth = 0;

    /**
     * Constructor
     *
     * @param array|\Traversable $options
     * @throws RendererCreationException
     */
    public function __construct($options = null)
    {
        if (!function_exists('gd_info')) {
            throw new RendererCreationException(__CLASS__ . ' requires the GD extension');
        }

        parent::__construct($options);
    }

    /**
     * Set height of the result image
     *
     * @param null|int $value
     * @throws Exception\OutOfRangeException
     * @return Image
     */
    public function setHeight($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            throw new Exception\OutOfRangeException(
                'Image height must be greater than or equals 0'
            );
        }
        $this->userHeight = intval($value);
        return $this;
    }

    /**
     * Get barcode height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->userHeight;
    }

    /**
     * Set barcode width
     *
     * @param mixed $value
     * @throws Exception\OutOfRangeException
     * @return self
     */
    public function setWidth($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            throw new Exception\OutOfRangeException(
                'Image width must be greater than or equals 0'
            );
        }
        $this->userWidth = intval($value);
        return $this;
    }

    /**
     * Get barcode width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->userWidth;
    }

    /**
     * Set an image resource to draw the barcode inside
     *
     * @param resource $image
     * @return Image
     * @throws Exception\InvalidArgumentException
     */
    public function setResource($image)
    {
        if (gettype($image) != 'resource' || get_resource_type($image) != 'gd') {
            throw new Exception\InvalidArgumentException(
                'Invalid image resource provided to setResource()'
            );
        }
        $this->resource = $image;
        return $this;
    }

    /**
     * Set the image type to produce (png, jpeg, gif)
     *
     * @param string $value
     * @throws Exception\InvalidArgumentException
     * @return Image
     */
    public function setImageType($value)
    {
        if ($value == 'jpg') {
            $value = 'jpeg';
        }

        if (!in_array($value, $this->allowedImageType)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided to setImageType()',
                $value
            ));
        }

        $this->imageType = $value;
        return $this;
    }

    /**
     * Retrieve the image type to produce
     *
     * @return string
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * Initialize the image resource
     *
     * @return void
     */
    protected function initRenderer()
    {
        $barcodeWidth  = $this->barcode->getWidth(true);
        $barcodeHeight = $this->barcode->getHeight(true);

        if ($this->resource !== null) {
            $foreColor       = $this->barcode->getForeColor();
            $backgroundColor = $this->barcode->getBackgroundColor();
            $this->imageBackgroundColor = imagecolorallocate(
                $this->resource,
                ($backgroundColor & 0xFF0000) >> 16,
                ($backgroundColor & 0x00FF00) >> 8,
                $backgroundColor & 0x0000FF
            );
            $this->imageForeColor = imagecolorallocate(
                $this->resource,
                ($foreColor & 0xFF0000) >> 16,
                ($foreColor & 0x00FF00) >> 8,
                $foreColor & 0x0000FF
            );
        } else {
            $width = $barcodeWidth;
            $height = $barcodeHeight;
            if ($this->userWidth && $this->barcode->getType() != 'error') {
                $width = $this->userWidth;
            }
            if ($this->userHeight && $this->barcode->getType() != 'error') {
                $height = $this->userHeight;
            }

            $foreColor       = $this->barcode->getForeColor();
            $backgroundColor = $this->barcode->getBackgroundColor();
            $this->resource = imagecreatetruecolor($width, $height);

            $this->imageBackgroundColor = imagecolorallocate(
                $this->resource,
                ($backgroundColor & 0xFF0000) >> 16,
                ($backgroundColor & 0x00FF00) >> 8,
                $backgroundColor & 0x0000FF
            );
            $this->imageForeColor = imagecolorallocate(
                $this->resource,
                ($foreColor & 0xFF0000) >> 16,
                ($foreColor & 0x00FF00) >> 8,
                $foreColor & 0x0000FF
            );
            $white = imagecolorallocate($this->resource, 255, 255, 255);
            imagefilledrectangle($this->resource, 0, 0, $width - 1, $height - 1, $white);
        }
        $this->adjustPosition(imagesy($this->resource), imagesx($this->resource));
        imagefilledrectangle($this->resource,
                             $this->leftOffset,
                             $this->topOffset,
                             $this->leftOffset + $barcodeWidth - 1,
                             $this->topOffset + $barcodeHeight - 1,
                             $this->imageBackgroundColor);
    }

    /**
     * Check barcode parameters
     *
     * @return void
     */
    protected function checkSpecificParams()
    {
        $this->checkDimensions();
    }

    /**
     * Check barcode dimensions
     *
     * @throws Exception\RuntimeException
     * @return void
     */
    protected function checkDimensions()
    {
        if ($this->resource !== null) {
            if (imagesy($this->resource) < $this->barcode->getHeight(true)) {
                throw new Exception\RuntimeException(
                    'Barcode is define outside the image (height)'
                );
            }
        } else {
            if ($this->userHeight) {
                $height = $this->barcode->getHeight(true);
                if ($this->userHeight < $height) {
                    throw new Exception\RuntimeException(sprintf(
                        "Barcode is define outside the image (calculated: '%d', provided: '%d')",
                        $height,
                        $this->userHeight
                    ));
                }
            }
        }
        if ($this->resource !== null) {
            if (imagesx($this->resource) < $this->barcode->getWidth(true)) {
                throw new Exception\RuntimeException(
                    'Barcode is define outside the image (width)'
                );
            }
        } else {
            if ($this->userWidth) {
                $width = $this->barcode->getWidth(true);
                if ($this->userWidth < $width) {
                    throw new Exception\RuntimeException(sprintf(
                        "Barcode is define outside the image (calculated: '%d', provided: '%d')",
                        $width,
                        $this->userWidth
                    ));
                }
            }
        }
    }

    /**
     * Draw and render the barcode with correct headers
     *
     * @return mixed
     */
    public function render()
    {
        $this->draw();
        header("Content-Type: image/" . $this->imageType);
        $functionName = 'image' . $this->imageType;
        $functionName($this->resource);

        ErrorHandler::start(E_WARNING);
        imagedestroy($this->resource);
        ErrorHandler::stop();
    }

    /**
     * Draw a polygon in the image resource
     *
     * @param array $points
     * @param int $color
     * @param  bool $filled
     */
    protected function drawPolygon($points, $color, $filled = true)
    {
        $newPoints = array($points[0][0] + $this->leftOffset,
                           $points[0][1] + $this->topOffset,
                           $points[1][0] + $this->leftOffset,
                           $points[1][1] + $this->topOffset,
                           $points[2][0] + $this->leftOffset,
                           $points[2][1] + $this->topOffset,
                           $points[3][0] + $this->leftOffset,
                           $points[3][1] + $this->topOffset,   );

        $allocatedColor = imagecolorallocate($this->resource,
                                             ($color & 0xFF0000) >> 16,
                                             ($color & 0x00FF00) >> 8,
                                              $color & 0x0000FF         );

        if ($filled) {
            imagefilledpolygon($this->resource, $newPoints, 4, $allocatedColor);
        } else {
            imagepolygon($this->resource, $newPoints, 4, $allocatedColor);
        }
    }

    /**
     * Draw a polygon in the image resource
     *
     * @param string $text
     * @param float $size
     * @param array $position
     * @param string $font
     * @param int $color
     * @param string $alignment
     * @param float $orientation
     * @throws Exception\RuntimeException
     */
    protected function drawText($text, $size, $position, $font, $color, $alignment = 'center', $orientation = 0)
    {
        $allocatedColor = imagecolorallocate($this->resource,
                                             ($color & 0xFF0000) >> 16,
                                             ($color & 0x00FF00) >> 8,
                                              $color & 0x0000FF         );

        if ($font == null) {
            $font = 3;
        }
        $position[0] += $this->leftOffset;
        $position[1] += $this->topOffset;

        if (is_numeric($font)) {
            if ($orientation) {
                /**
                 * imagestring() doesn't allow orientation, if orientation
                 * needed: a TTF font is required.
                 * Throwing an exception here, allow to use automaticRenderError
                 * to informe user of the problem instead of simply not drawing
                 * the text
                 */
                throw new Exception\RuntimeException(
                    'No orientation possible with GD internal font'
                );
            }
            $fontWidth = imagefontwidth($font);
            $positionY = $position[1] - imagefontheight($font) + 1;
            switch ($alignment) {
                case 'left':
                    $positionX = $position[0];
                    break;
                case 'center':
                    $positionX = $position[0] - ceil(($fontWidth * strlen($text)) / 2);
                    break;
                case 'right':
                    $positionX = $position[0] - ($fontWidth * strlen($text));
                    break;
            }
            imagestring($this->resource, $font, $positionX, $positionY, $text, $color);
        } else {

            if (!function_exists('imagettfbbox')) {
                throw new Exception\RuntimeException(
                    'A font was provided, but this instance of PHP does not have TTF (FreeType) support');
            }

            $box = imagettfbbox($size, 0, $font, $text);
            switch ($alignment) {
                case 'left':
                    $width = 0;
                    break;
                case 'center':
                    $width = ($box[2] - $box[0]) / 2;
                    break;
                case 'right':
                    $width = ($box[2] - $box[0]);
                    break;
            }
            imagettftext($this->resource,
                         $size,
                         $orientation,
                         $position[0] - ($width * cos(pi() * $orientation / 180)),
                         $position[1] + ($width * sin(pi() * $orientation / 180)),
                         $allocatedColor,
                         $font,
                         $text);
        }
    }
}
