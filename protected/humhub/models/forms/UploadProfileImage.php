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
        return array(
            array('image', 'required'),
            array('image', 'file', 'extensions' => 'jpg, png, jpeg, tiff', 'maxSize' => 3 * 1024 * 1024),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'image' => Yii::t('base', 'New profile image'),
        );
    }

}
