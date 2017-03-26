<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models\forms;

use Yii;
use yii\base\Model;

/**
 * ChooseLanguage is the model of the language select box to change language for
 * guests.
 */
class ChooseLanguage extends Model
{

    /**
     * @var string the language
     */
    public $language;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'language' => Yii::t('base', 'Language'),
        );
    }

    /**
     * Stores language as cookie
     *
     * @since 1.2
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $cookie = new \yii\web\Cookie([
                'name' => 'language',
                'value' => $this->language,
                'expire' => time() + 86400 * 365,
            ]);
            Yii::$app->getResponse()->getCookies()->add($cookie);

            return true;
        }

        return false;
    }

    /**
     * Returns the saved language in the cookie
     *
     * @return string the stored language
     */
    public function getSavedLanguage()
    {
        if (isset(Yii::$app->request->cookies['language'])) {
            $this->language = (string) Yii::$app->request->cookies['language'];

            if (!$this->validate()) {
                // Invalid cookie
                $cookie = new \yii\web\Cookie([
                    'name' => 'language',
                    'value' => 'en',
                    'expire' => 1,
                ]);
                Yii::$app->getResponse()->getCookies()->add($cookie);
            } else {
                return $this->language;
            }
        }

        return null;
    }

}