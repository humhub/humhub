<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\models;

use humhub\modules\user\components\CheckPasswordValidator;
use Yii;
use yii\base\Model;

/**
 * Form Model for Space Deletion
 *
 * @since 0.5
 */
class DeleteForm extends Model
{

    /**
     * @var string users password
     */
    public $currentPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['currentPassword', 'required'],
            ['currentPassword', CheckPasswordValidator::class]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'currentPassword' => Yii::t('SpaceModule.forms_SpaceDeleteForm', 'Your password'),
        ];
    }

}
