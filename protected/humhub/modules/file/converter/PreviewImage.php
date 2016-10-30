<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use Yii;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\ImageConverter;

/**
 * PreviewImage Converter
 *
 * @since 1.2
 * @author Luke
 */
class PreviewImage extends BaseConverter
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->options['mode'] = 'max';
        $maxPreviewImageWidth = Yii::$app->getModule('file')->settings->get('maxPreviewImageWidth');
        $maxPreviewImageHeight = Yii::$app->getModule('file')->settings->get('maxPreviewImageHeight');

        $this->options['width'] = $maxPreviewImageWidth ? $maxPreviewImageWidth : 200;
        $this->options['height'] = $maxPreviewImageHeight ? $maxPreviewImageHeight : 200;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function convert($fileName)
    {
        if (!is_file($this->file->store->get($fileName))) {
            ImageConverter::Resize($this->file->store->get(), $this->file->store->get($fileName), $this->options);
        }
    }

    /**
     * @inheritdoc
     */
    protected function canConvert(File $file)
    {
        $originalFile = $file->store->get();

        if (substr($file->mime_type, 0, 6) !== 'image/' || !is_file($originalFile)) {
            return false;
        }

        $imageInfo = @getimagesize($originalFile);

        // Check if we got any dimensions - invalid image
        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return false;
        }

        // Check if image type is supported
        if ($imageInfo[2] != IMAGETYPE_PNG && $imageInfo[2] != IMAGETYPE_JPEG && $imageInfo[2] != IMAGETYPE_GIF) {
            return false;
        }


        return true;
    }

}
