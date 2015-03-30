<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details. 
 */

/**
 * ImageConverter provides a simple interface for converting or resizing images.
 * 
 * @package humhub.modules_core.file.libs
 * @since 0.5
 */
class ImageConverter
{

    /**
     * Transforms given File to Jpeg
     * 
     * @param String $sourceFile
     * @param String $targetFile
     * @param String $originalFileName (provide this when sourceFile has no extension e.g. tempfile)
     */
    public static function TransformToJpeg($sourceFile, $targetFile)
    {

        if (HSetting::Get('imageMagickPath', 'file')) {
            $convertCommand = HSetting::Get('imageMagickPath', 'file');
            $command = $convertCommand . " \"{$sourceFile}\" \"{$targetFile}\"";
            $ret = passthru($command);
        } else {
            $gdImage = self::getGDImageByFile($sourceFile);
            $gdImage = self::fixOrientation($gdImage, $sourceFile);
            imagejpeg($gdImage, $targetFile, 100);
            imagedestroy($gdImage);
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

        if (HSetting::Get('imageMagickPath', 'file')) {
            self::ResizeImageMagick($sourceFile, $targetFile, $options);
        } else {
            self::ResizeGD($sourceFile, $targetFile, $options);
        }
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
                    $crop = ImageCreateTrueColor($cropW, $cropH);
                    ImageCopy(
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
        $convertCommand = HSetting::Get('imageMagickPath', 'file');
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

        list($width, $height, $imageType) = getimagesize($fileName);

        switch ($imageType) {
            case IMAGETYPE_PNG:
                $gdImage = imagecreatefrompng($fileName);
                break;
            case IMAGETYPE_GIF:
                $gdImage = imagecreatefromgif($fileName);
                break;
            case IMAGETYPE_JPEG:
                $gdImage = imagecreatefromjpeg($fileName);
                break;
        }

        return $gdImage;
    }

    public static function fixOrientation($image, $filename)
    {
        $exif = @exif_read_data($filename);
        if (is_array($exif) && !empty($exif['Orientation'])) {
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
