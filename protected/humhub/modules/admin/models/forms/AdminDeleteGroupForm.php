<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.forms
 * @since 0.5
 */
class AdminDeleteGroupForm extends \yii\base\Model
{

    public $group_id;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('group_id', 'required'),
#			array('group_id', 'integer'),
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
            'group_id' => Yii::t('AdminModule.forms_AdminDeleteGroupForm', 'Group'),
        );
    }

}
