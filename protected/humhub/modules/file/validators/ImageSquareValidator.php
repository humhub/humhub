<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\validators;

use Yii;
use yii\web\UploadedFile;

/**
 * ImageSquareValidator checks if uploaded image is squared.
 *
 * @inheritdoc
 */
class ImageSquareValidator extends \yii\validators\FileValidator
{
    /**
     * @var string the error message used when the uploaded file is not a squared image.
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     */
    public $noSquaredImage;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->noSquaredImage === null) {
            $this->noSquaredImage = Yii::t('FileModule.base', 'The uploaded image is not a squared.');
        }

    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $result = parent::validateValue($value);
        return empty($result) ? $this->validateImage($value) : $result;
    }

    /**
     * Validates an image file.
     *
     * @param UploadedFile $image uploaded file passed to check against a set of rules
     * @return array|null the error message and the parameters to be inserted into the error message.
     * Null should be returned if the data is valid.
     */
    protected function validateImage($image)
    {
        if (!$this->isSquared($image)) {
            return [$this->noSquaredImage, ['file' => $image->name]];
        }

        return null;
    }


    private function isSquared($image)
    {
        if (false === ($imageInfo = getimagesize($image->tempName))) {
            return false;
        }

        list($width, $height) = $imageInfo;
        if ($width == 0 || $height == 0) {
            return false;
        }

        if ($width != $height) {
            return false;
        }

        return true;

    }

}
