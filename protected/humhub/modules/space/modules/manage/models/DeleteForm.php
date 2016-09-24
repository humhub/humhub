<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\models;

use Yii;
use yii\base\Model;
use humhub\modules\user\components\CheckPasswordValidator;

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
        return array(
            array('currentPassword', 'required'),
            array('currentPassword', CheckPasswordValidator::className()),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'currentPassword' => Yii::t('SpaceModule.forms_SpaceDeleteForm', 'Your password'),
        );
    }

}
