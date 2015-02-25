<?php 

class ChooseLanguageForm extends CFormModel {

    
    public $language;

    /**
     * Declares the validation rules.
     *
     * @return Array Validation Rules
     */
    public function rules() {
        return array(
            array('language', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z_]/', 'message' => Yii::t('base', 'Invalid language!')),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'language' => Yii::t('base', 'Language'),
        );
    }

}
?>