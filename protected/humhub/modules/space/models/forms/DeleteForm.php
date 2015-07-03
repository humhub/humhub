<?php

namespace humhub\modules\space\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\user\components\CheckPasswordValidator;

/**
 * Form Model for Space Deletion
 *
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class DeleteForm extends Model
{

    public $currentPassword;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('currentPassword', 'required'),
            array('currentPassword', CheckPasswordValidator::className()),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'currentPassword' => Yii::t('SpaceModule.forms_SpaceDeleteForm', 'Your password'),
        );
    }

}
