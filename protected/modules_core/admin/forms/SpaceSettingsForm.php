<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.10
 */
class SpaceSettingsForm extends CFormModel
{

    public $defaultVisibility;
    public $defaultJoinPolicy;
    public $defaultPostPolicy;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('defaultVisibility, defaultJoinPolicy, defaultPostPolicy', 'safe'),
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
            'defaultPostPolicy' => Yii::t('AdminModule.forms_SpaceSettingsForm', 'Default Post Policy'),
        );
    }

}
