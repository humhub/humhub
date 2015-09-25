<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.forms
 * @since 0.5
 */
class SpaceTypeDelete extends \yii\base\Model
{

    public $space_type_id;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('space_type_id', 'required'),
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
            'space_type_id' => Yii::t('AdminModule.models_forms_SpaceTypeDelete', 'Space Type'),
        );
    }

}
