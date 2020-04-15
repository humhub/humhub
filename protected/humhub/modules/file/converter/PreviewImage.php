<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use Imagine\Image\ImageInterface;
use Yii;
use humhub\modules\file\models\File;
use humhub\libs\Html;
use yii\imagine\Image;

/**
 * PreviewImage Converter
 *
 * @since 1.2
 * @author Luke
 */
class PreviewImage extends BaseConverter
{

    /**
     * @var ImageInterface
     */
    public $image;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $maxPreviewImageWidth = Yii::$app->getModule('file')->settings->get('maxPreviewImageWidth');
        $maxPreviewImageHeight = Yii::$app->getModule('file')->settings->get('maxPreviewImageHeight');

        $this->options['width'] = $maxPreviewImageWidth ? $maxPreviewImageWidth : 200;
        $this->options['height'] = $maxPreviewImageHeight ? $maxPreviewImageHeight : 200;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function render($file = null)
    {
        if ($file) {
            $this->applyFile($file);
        }

        // Provide the natural height so the browser will include a placeholder height. Todo: smooth image loading
        return Html::img($this->getUrl(), ['class' => 'animated fadeIn', 'height' => $this->height, 'alt' => $this->getAltText()]);
    }


    protected function getAltText($file = null)
    {
        if ($file) {
            return Html::encode($file->file_name);
        } elseif ($this->file) {
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
            $image = Image::getImagine()->open($this->file->store->get());

            if ($image->getSize()->getHeight() > $this->options['height']) {
                $image->resize($image->getSize()->heighten($this->options['height']));
            }

            if ($image->getSize()->getWidth() > $this->options['width']) {
                $image->resize($image->getSize()->widen($this->options['width']));
            }

            $image->save($this->file->store->get($fileName), ['format' => 'png']);
        }


        $this->image = Image::getImagine()->open($this->file->store->get($fileName));
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

        try {
            Image::getImagine()->open($originalFile)->getSize();
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * @return int the image width or 0 if not valid
     */
    public function getWidth()
    {
        if ($this->image !== null) {
            return $this->image->getSize()->getWidth();
        }
        return 0;
    }

    /**
     * @return int the image height or 0 if not valid
     */
    public function getHeight()
    {
        if ($this->image !== null) {
            return $this->image->getSize()->getHeight();
        }
        return 0;
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
