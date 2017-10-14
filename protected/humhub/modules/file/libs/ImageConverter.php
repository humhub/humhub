<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\libs\Helpers;
use Yii;
use yii\base\Exception;

/**
 * ImageConverter provides a simple interface for converting or resizing images.
 *
 * @since 0.5
 */
class ImageConverter
{

    /** Max value of memory allowed to be allocated additional to the currently set memory limit in php.ini in MBytes. * */
    const DEFAULT_MAX_ADDITIONAL_MEMORY_ALLOCATION = 64;
    const SETTINGS_NAME_MAX_MEMORY_ALLOCATION = 'maxImageProcessingMemoryAllocation';

    /**
     * Transforms given File to Jpeg
     *
     * @param String $sourceFile
     * @param String $targetFile
     * @param String $originalFileName (provide this when sourceFile has no extension e.g. tempfile)
     */
    public static function TransformToJpeg($sourceFile, $targetFile)
    {

        if (Yii::$app->getModule('file')->settings->get('imageMagickPath')) {
            $convertCommand = Yii::$app->getModule('file')->settings->get('imageMagickPath');
            $command = $convertCommand . " -auto-orient \"{$sourceFile}\" \"{$targetFile}\"";
            $ret = passthru($command);
        } else {
            $gdImage = self::getGDImageByFile($sourceFile);

            if ($gdImage !== null) {
                $gdImage = self::fixOrientation($gdImage, $sourceFile);
                imagejpeg($gdImage, $targetFile, 100);
                imagedestroy($gdImage);
            }
        }

        return true;
    }

    /**
     * Resizes an given Image to an given Size
     *
     * Options Array:
     *      width - in px
     *      height - in px
     *      mode:
     *          force - creates image with given dimensions  (default)
     *          max - creates image with a maximum of given width / height
     *
     * @param String $sourceFile
     * @param String $targetFile
     * @param Array $options
     */
    public static function Resize($sourceFile, $targetFile, $options = array())
    {

        if (!isset($options['width']))
            $options['width'] = 0;

        if (!isset($options['height']))
            $options['height'] = 0;

        if (!isset($options['mode']))
            $options['mode'] = 'force';

        if (Yii::$app->getModule('file')->settings->get('imageMagickPath')) {
            self::ResizeImageMagick($sourceFile, $targetFile, $options);
        } else {
            // dynamically allocate memory to process image
            $memoryLimit = ini_get('memory_limit');
            self::allocateMemory($sourceFile);
            self::ResizeGD($sourceFile, $targetFile, $options);
            ini_set('memory_limit', $memoryLimit);
        }
    }

    /**
     * Dynamically allocate enough memory to process the given image.
     *
     * @throws Exception if the memory is not sufficient to process the image.
     * @param String $sourceFile the source file.
     * @param boolean $test if true the memory will not really be allocated and no exception will be thrown.
     * @return boolean true if sufficient memory is available.
     */
    public static function allocateMemory($sourceFile, $test = false)
    {
        if (!file_exists($sourceFile)) {
            return true;
        }

        // getting the image width and height
        list($width, $height) = getimagesize($sourceFile);

        // buffer for memory needed by other stuff
        $buffer = 10;
        // usually for RGB 3 pixels are used, for CMYK 4 pixels
        $bytesPerPixel = 4;
        // tweak factor, experience value
        $tweakFactor = 2.2;
        // check if the file exists, if not it seems that we do not have to allocate memory and we return true

        // get defined memory limit from php_ini
        $memoryLimit = ini_get('memory_limit');

        // No memory limit set
        if ($memoryLimit == -1) {
            return false;
        }

        // calc needed size for processing image dimensions in MegaBytes.
        $memoryLimit = Helpers::getBytesOfIniValue($memoryLimit) / 1048576;
        // calc needed size for processing image dimensions in MegaBytes.
        $neededMemory = floor(($width * $height * $bytesPerPixel * $tweakFactor + 1048576) / 1048576);
        $maxMemoryAllocation = Yii::$app->getModule('file')->settings->get(self::SETTINGS_NAME_MAX_MEMORY_ALLOCATION);
        $maxMemoryAllocation = $maxMemoryAllocation == null ? self::DEFAULT_MAX_ADDITIONAL_MEMORY_ALLOCATION : $maxMemoryAllocation;
        $newMemoryLimit = $memoryLimit + min($neededMemory, $maxMemoryAllocation);

        // dynamically allocate memory to process image
        $result = ini_set('memory_limit', $newMemoryLimit . 'M');
        // check if we were able to set memory_limit with ini_set with the current server configuration
        $failure = (version_compare(PHP_VERSION, '5.3.0') >= 0) ? false : '';

        $allocatedMemory = $result == $failure ? $memoryLimit : $newMemoryLimit;

        if ($neededMemory + $buffer < $allocatedMemory) {
            return true;
        }
        if (!$test) {
            throw new Exception("Image $sourceFile too large to be resized. Increase MAX_MEMORY_USAGE");
        }
        return false;
    }

    /**
     * Resize GD Libary Implementation
     *
     * @param type $sourceFile
     * @param type $targetFile
     * @param type $options
     */
    private static function ResizeGD($sourceFile, $targetFile, $options = array())
    {

        $width = $options['width'];
        $height = $options['height'];

        $gdImage = self::getGDImageByFile($sourceFile);

        if ($gdImage === null) {
            return;
        }

        $gdImage = self::fixOrientation($gdImage, $sourceFile);

        $sourceWidth = imagesx($gdImage);
        $sourceHeight = imagesy($gdImage);

        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $dst_w = $width;
        $dst_h = $height;
        $src_w = $sourceWidth;
        $src_h = $sourceHeight;

        if ($options['mode'] == 'max') {

            if ($sourceHeight > $height || $sourceWidth > $width) {

                // http://snipplr.com/view/53183

                if ($height == 0)
                    $height = $sourceHeight;
                if ($width == 0)
                    $width = $sourceWidth;

                $w = $sourceWidth;
                $h = $sourceHeight;
                $max_w = $width;
                $max_h = $height;

                // $w is the width of the current rectangle
                // $h is the height of the current rectangle
                // $max_w is the maximum width that an image can be sized
                // $max_h is the maximum height that an image can be sized
                // **** Here's where the magic is starts ****
                // Switch the concept of horiz/vertical/square to long/short side
                $short_side_len = ($w < $h ? $w : $h);
                $long_side_len = ($w > $h ? $w : $h);
                // Set a variable to the variable name of the output variable
                $ssvar = ($w > $h ? 'h' : 'w');
                $lsvar = ($w > $h ? 'w' : 'h');
                $maxLSvar = "max_" . $lsvar;
                $maxSSvar = "max_" . $ssvar;

                // Do the first pass on the long side
                $ratio = $$maxLSvar / $long_side_len;
                $newSS = round($short_side_len * $ratio);
                $newLS = round($long_side_len * $ratio);

                // *** Note - the only coditional block!
                // If short side is still out of limit, limit the short side and adjust
                if ($newSS > $$maxSSvar) {
                    $ratio = $$maxSSvar / $newSS;
                    $newLS = round($ratio * $newLS);
                    $newSS = $$maxSSvar;
                }

                // **** Here's where the magic ends ****
                // Re-couple the h/w (or w/h) with the long/shortside counterparts
                // $$ means it's a variable variable (dynamic assignment)
                $$ssvar = $newSS;
                $$lsvar = $newLS;

                // Prep the return array
                #$dimensions['w'] = $w; // this is derived from either $ssvar or $lsvar
                #$dimensions['h'] = $h;

                $width = $w;
                $height = $h;
                $dst_h = $h;
                $dst_w = $w;
            } else {
                $height = $sourceHeight;
                $width = $sourceWidth;
                $dst_h = $sourceHeight;
                $dst_w = $sourceWidth;
            }
        } else if ($options['mode'] == 'force') {

            // When ratio not fit, crop it - requires given width & height
            if ($width != 0 && $height != 0) {
                if (($sourceWidth / $sourceHeight) != ($width / $height)) {

                    $_scale = min((float) ($sourceWidth / $width), (float) ($sourceHeight / $height));
                    $cropX = (float) ($sourceWidth - ($_scale * $width));
                    $cropY = (float) ($sourceHeight - ($_scale * $height));

                    // cropped image size
                    $cropW = (float) ($sourceWidth - $cropX);
                    $cropH = (float) ($sourceHeight - $cropY);

                    // crop the middle part of the image to fit proportions
                    $crop = imagecreatetruecolor($cropW, $cropH);
                    imagecopy(
                            $crop, $gdImage, 0, 0, (int) ($cropX / 2), (int) ($cropY / 2), $cropW, $cropH
                    );

                    $src_w = $cropW;
                    $src_h = $cropH;

                    imagecopy($gdImage, $crop, 0, 0, 0, 0, $src_w, $src_h);
                }
            } elseif ($width == 0) {
                $width = $sourceWidth;
            } elseif ($height == 0) {
                $height = $sourceHeight;
            } else {
                $width = $sourceWidth;
                $height = $sourceHeight;
            }
        }

        // Create new Image
        $newGdImage = imagecreatetruecolor($width, $height);

        if (isset($options['transparent']) && $options['transparent']) {
            imagealphablending($newGdImage, false);
            imagesavealpha($newGdImage, true);
            $transparent = imagecolorallocatealpha($newGdImage, 255, 255, 255, 127);
            imagefilledrectangle($newGdImage, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled($newGdImage, $gdImage, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        list($hw, $hx, $imageType) = getimagesize($sourceFile);

        switch ($imageType) {
            case IMAGETYPE_PNG:
                imagepng($newGdImage, $targetFile);
                break;
            case IMAGETYPE_GIF:
                imagegif($newGdImage, $targetFile);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($newGdImage, $targetFile, 100);
                break;
        }
        imagedestroy($gdImage);
        imagedestroy($newGdImage);
    }

    /**
     * Resize Image Magick Implementation
     *
     * @param type $sourceFile
     * @param type $targetFile
     * @param type $options
     */
    private static function ResizeImageMagick($sourceFile, $targetFile, $options = array())
    {
        $convertCommand = Yii::$app->getModule('file')->settings->get('imageMagickPath');
        $width = (int) $options['width'];
        $height = (int) $options['height'];

        if ($options['mode'] == 'max') {

            if ($width && $height)
                $command = $convertCommand . "  -quality 100 -density 300 \"{$sourceFile}\" -resize '{$width}x{$height}>' \"{$targetFile}\"";
            elseif ($width)
                $command = $convertCommand . "  -quality 100 -density 300 \"{$sourceFile}\" -resize '{$width}x>' \"{$targetFile}\"";
            elseif ($height)
                $command = $convertCommand . "  -quality 100 -density 300 \"{$sourceFile}\" -resize 'x{$height}>' \"{$targetFile}\"";

            $ret = passthru($command);
        } elseif ($options['mode'] == 'force') {
            $command = $convertCommand . " \"{$sourceFile}\" -gravity center -quality 100 -resize {$width}x{$height}^ -extent {$width}x{$height}  \"{$targetFile}\"";
            $ret = passthru($command);
        }
    }

    /**
     * Creates GD Image Resource by given Filename
     *
     * @param String $fileName
     * @return resource GD Image
     */
    public static function getGDImageByFile($fileName)
    {
        $gdImage = null;

        list($width, $height, $imageType) = getimagesize($fileName);

        try {
            switch ($imageType) {
                case IMAGETYPE_PNG:
                    $gdImage = imagecreatefrompng($fileName);
                    break;
                case IMAGETYPE_GIF:
                    $gdImage = imagecreatefromgif($fileName);
                    break;
                case IMAGETYPE_JPEG:
                    $gdImage = @imagecreatefromjpeg($fileName);
                    if (!$gdImage) {
                        $gdImage = imagecreatefromstring(file_get_contents($fileName));
                    }
                    break;
            }
        } catch (\Exception $ex) {
            Yii::warning('Could not get GD Image by file: ' . $fileName . ' - Error: ' . $ex->getMessage());
            return null;
        }

        return $gdImage;
    }

    public static function fixOrientation($image, $filename)
    {
        $exif = @exif_read_data($filename);
        $memoryLimit = ini_get('memory_limit');
        if (is_array($exif) && !empty($exif['Orientation'])) {
            // dynamically allocate memory to process image
            self::allocateMemory($filename);
            switch ($exif['Orientation']) {
                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;
                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;
            }
        }
        ini_set('memory_limit', $memoryLimit);
        return $image;
    }

    public static function checkTransparent($im)
    {

        $im = self::getGDImageByFile($im);

        $width = imagesx($im); // Get the width of the image
        $height = imagesy($im); // Get the height of the image
        // We run the image pixel by pixel and as soon as we find a transparent pixel we stop and return true.
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                $rgba = imagecolorat($im, $i, $j);
                if (($rgba & 0x7F000000) >> 24) {
                    return true;
                }
            }
        }

        // If we dont find any pixel the function will return false.
        return false;
    }

}
