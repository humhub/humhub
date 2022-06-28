<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
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
    public $blockedUsers;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tags', 'blockedUsers'], 'safe'],
            [['show_introduction_tour'], 'boolean'],
            [['timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
            ['visibility', 'in', 'range' => array_keys(User::getVisibilityOptions(false)),
                'when' => function () {return AuthHelper::isGuestAccessEnabled();}],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tags' => Yii::t('UserModule.account', 'Profile Tags'),
            'language' => Yii::t('UserModule.account', 'Language'),
            'show_introduction_tour' => Yii::t('UserModule.account', 'Hide introduction tour panel on dashboard'),
            'timeZone' => Yii::t('UserModule.account', 'TimeZone'),
            'visibility' => Yii::t('UserModule.account', 'Profile visibility'),
            'blockedUsers' => Yii::t('UserModule.account', 'Blocked users'),
        ];
    }

    public function attributeHints()
    {
        return [
            'tags' => Yii::t('UserModule.account', 'Add tags to your profile describing you and highlighting your skills and interests. Your tags will be displayed in your profile and in the \'People\' directory.'),
        ];
    }

    public function getTags(): array
    {
        return is_array($this->tags) ? $this->tags : [];
    }

}
