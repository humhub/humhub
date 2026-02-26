<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use Exception;
use humhub\helpers\Html;
use humhub\modules\admin\models\Log;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use Imagine\Image\ImageInterface;
use Yii;
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
    private $_image;

    /**
     * @var ImageInterface
     */
    private $_imageFile;


    /**
     * @inheritdoc
     */
    public function init()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        $this->options['width'] = $module->imagePreviewMaxWidth;
        $this->options['height'] = $module->imagePreviewMaxHeight;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return 'preview-image';
    }

    /**
     * @inheritdoc
     */
    public function render($file = null)
    {
        if ($file) {
            $this->applyFile($file);
        }

        return Html::img($this->getUrl(), ['class' => 'animated fadeIn', 'alt' => $this->getAltText()]);
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
        try {
            if (!is_file($this->file->store->get($fileName))) {
                $image = Image::getImagine()->open($this->file->store->get());
                ImageHelper::fixJpegOrientation($image, $this->file);

                if ($image->getSize()->getHeight() > $this->options['height']) {
                    $image->resize($image->getSize()->heighten($this->options['height']));
                }

                if ($image->getSize()->getWidth() > $this->options['width']) {
                    $image->resize($image->getSize()->widen($this->options['width']));
                }

                $options = ['format' => 'png'];
                if (!($image instanceof \Imagine\Gd\Image) && count($image->layers()) > 1) {
                    $options = ['format' => 'gif', 'animated' => true];
                }

                $image->save($this->file->store->get($fileName), $options);
            }
        } catch (Exception $ex) {
            $message = 'Could not convert file with id ' . $this->file->id . '. Error: ' . $ex->getMessage();
            $count = Log::find()->where(['message' => $message])->count();

            if ($count == 0) {
                Yii::warning($message);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function canConvert(File $file)
    {
        $originalFile = $file->store->get();

        if (!str_starts_with($file->mime_type, 'image/') || !is_file($originalFile)) {
            return false;
        }

        return true;
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
