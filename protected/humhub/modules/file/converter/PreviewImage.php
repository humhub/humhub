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
use humhub\libs\Html;

/**
 * PreviewImage Converter
 *
 * @since 1.2
 * @author Luke
 */
class PreviewImage extends BaseConverter
{

    public $imageInfo;

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

    public function render($file = null)
    {
        if ($file) {
            $this->applyFile($file);
        }
        
        // Provide the natural height so the browser will include a placeholder height. Todo: smooth image loading
        return \yii\helpers\Html::img($this->getUrl(), ['class' => 'animated fadeIn', 'height' => $this->height, 'alt' => $this->getAltText()]);
    }
    
    protected function getAltText($file = null)
    {
        if ($file) {
            return Html::encode($file->file_name);
        } else if($this->file) {
            return Html::encode($this->file->file_name);
        }
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function convert($fileName)
    {
        if (!is_file($this->file->store->get($fileName))) {
            ImageConverter::Resize($this->file->store->get(), $this->file->store->get($fileName), $this->options);
        }

        $this->imageInfo = @getimagesize($this->file->store->get($fileName));
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

    public function getDimensions()
    {
        if (!$this->imageInfo || !isset($this->imageInfo[3])) {
            return;
        }

        return $this->imageInfo[3];
    }

    public function getWidth()
    {
        if (!$this->imageInfo || !isset($this->imageInfo[0])) {
            return 'auto';
        }

        return $this->imageInfo[0];
    }

    public function getHeight()
    {
        if (!$this->imageInfo || !isset($this->imageInfo[1])) {
            return 'auto';
        }

        return $this->imageInfo[1];
    }

    /**
     * Returns the gallery link to the original file
     * 
     * @param array $htmlOptions optional link html options
     * @return string the link
     */
    public function renderGalleryLink($htmlOptions = [])
    {
        return Html::a($this->render(), $this->file->getUrl(), array_merge($htmlOptions, ['data-ui-gallery' => 'gallery-' . $this->file->guid]));
    }

}
