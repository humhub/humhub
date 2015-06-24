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
    public $logo;

    /**
     * Declares the validation rules.
     */
    public function rules() {

        $themes = HTheme::getThemes();

        return array(
            array('paginationSize', 'numerical', 'integerOnly' => true, 'max'=>200, 'min'=>1),
            array('theme', 'in', 'range'=>$themes),
            array('displayName, spaceOrder', 'safe'),
            array('logo', 'file', 'types' => 'jpg, png, jpeg', 'maxSize' => 3 * 1024 * 1024, 'allowEmpty' => true),
            array('logo', 'dimensionValidation', 'skipOnError'=> true),
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
            'logo' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Logo upload')
        );
    }

    public function dimensionValidation($attribute, $param)
    {

        if (is_object($this->logo)) {

            list($width, $height) = getimagesize($this->logo->tempName);
            if ($height < 40)
                $this->addError('logo', 'Logo size should have at least 40px of height');
        }

    }

}