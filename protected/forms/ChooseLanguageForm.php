<?php

class ChooseLanguageForm extends CFormModel
{

    public $language;

    /**
     * Declares the validation rules.
     *
     * @return Array Validation Rules
     */
    public function rules()
    {
        return array(
            array('language', 'in', 'range' => array_keys(Yii::app()->params['availableLanguages'])),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'language' => Yii::t('base', 'Language'),
        );
    }

}

?>