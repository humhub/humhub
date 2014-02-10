<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class DesignSettingsForm extends CFormModel {

    public $theme;
    public $paginationSize;
    public $displayName;

    /**
     * Declares the validation rules.
     */
    public function rules() {

        $themes = HTheme::getThemes();

        return array(
            array('paginationSize', 'numerical', 'integerOnly' => true, 'max'=>200, 'min'=>1),
            array('theme', 'in', 'range'=>$themes),
            array('displayName', 'safe'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'theme' => Yii::t('AdminModule.base', 'Theme'),
            'paginationSize' => Yii::t('AdminModule.base', 'Default pagination size (Entries per page)'),
            'displayName' => Yii::t('AdminModule.base', 'Display Name (Format)'),
        );
    }

}