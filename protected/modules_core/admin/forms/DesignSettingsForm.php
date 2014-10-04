<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class DesignSettingsForm extends CFormModel {

    public $theme;
    public $paginationSize;
    public $displayName;
    public $spaceOrder;

    /**
     * Declares the validation rules.
     */
    public function rules() {

        $themes = HTheme::getThemes();

        return array(
            array('paginationSize', 'numerical', 'integerOnly' => true, 'max'=>200, 'min'=>1),
            array('theme', 'in', 'range'=>$themes),
            array('displayName, spaceOrder', 'safe'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'theme' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Theme'),
            'paginationSize' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Default pagination size (Entries per page)'),
            'displayName' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Display Name (Format)'),
            'spaceOrder' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Dropdown space order'),
        );
    }

}