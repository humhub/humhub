<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.10
 */
class SpaceSettingsForm extends \yii\base\Model
{

    public $defaultVisibility;
    public $defaultJoinPolicy;
    public $defaultContentVisibility;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array(['defaultVisibility', 'defaultJoinPolicy', 'defaultContentVisibility'], 'integer'),
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
            'defaultVisibility' => Yii::t('AdminModule.forms_SpaceSettingsForm', 'Default Visibility'),
            'defaultJoinPolicy' => Yii::t('AdminModule.forms_SpaceSettingsForm', 'Default Join Policy'),
            'defaultContentVisibility' => Yii::t('AdminModule.forms_SpaceSettingsForm', 'Default Content Visiblity'),
        );
    }

}
