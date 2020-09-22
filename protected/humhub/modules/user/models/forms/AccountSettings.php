<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Yii;

/**
 * Form Model for changing basic account settings
 *
 * @since 0.9
 */
class AccountSettings extends \yii\base\Model
{

    public $tags;
    public $language;
    public $show_introduction_tour;
    public $visibility;
    public $timeZone;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['tags', 'string', 'max' => 250],
            [['show_introduction_tour'], 'boolean'],
            [['timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
            ['visibility', 'in', 'range' => [1, 2]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tags' => Yii::t('UserModule.account', 'Tags'),
            'language' => Yii::t('UserModule.account', 'Language'),
            'show_introduction_tour' => Yii::t('UserModule.account', 'Hide introduction tour panel on dashboard'),
            'timeZone' => Yii::t('UserModule.account', 'TimeZone'),
            'visibility' => Yii::t('UserModule.account', 'Profile visibility'),
        ];
    }

}
