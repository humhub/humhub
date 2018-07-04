<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models\forms;

use Yii;
use yii\base\Model;

/**
 * UploadProfileImageForm allows uploads of profile images.
 *
 * Profile images will used by spaces or users.
 *
 * @package humhub.forms
 * @since 0.5
 */
class UploadProfileImage extends Model
{

    /**
     * @var String uploaded image
     */
    public $image;

    /**
     * Declares the validation rules.
     *
     * @return Array Validation Rules
     */
    public function rules()
    {
        return [
            ['image', 'required'],
            ['image', 'file', 'extensions' => 'jpg, jpeg, png, tiff', 'maxSize' => Yii::$app->getModule('file')->settings->get('maxFileSize')],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'image' => Yii::t('base', 'New profile image'),
        ];
    }

}
