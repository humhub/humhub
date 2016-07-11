<?php

namespace humhub\models\forms;

use Yii;
use yii\base\Model;

class ChooseLanguage extends Model
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
            array('language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())),
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