<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use DateTimeZone;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Form Model for changing basic account settings
 *
 * @since 0.9
 */
class AccountSettings extends Model
{
    public $tags;
    public $language;
    public $hideOnlineStatus;
    public $markdownEditorMode;
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
            [['hideOnlineStatus', 'show_introduction_tour'], 'boolean'],
            [['markdownEditorMode'], 'in', 'range' => [0, 1]],
            [['timeZone'], 'in', 'range' => DateTimeZone::listIdentifiers()],
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
            ['visibility', 'in', 'range' => array_keys($this->getVisibilityOptions()),
                'when' => function (self $model) {
                    return $model->isVisibilityViewable() && $model->isVisibilityEditable();
                }],
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
            'hideOnlineStatus' => Yii::t('UserModule.account', 'Hide my online status'),
            'markdownEditorMode' => Yii::t('UserModule.account', 'Markdown Editor Mode'),
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

    public function isHiddenUser(): bool
    {
        return Yii::$app->user->getIdentity()->visibility == User::VISIBILITY_HIDDEN;
    }

    public function isVisibilityViewable(): bool
    {
        return AuthHelper::isGuestAccessEnabled();
    }

    public function isVisibilityEditable(): bool
    {
        return Yii::$app->user->isAdmin()
            || ($this->isVisibilityViewable() && !$this->isHiddenUser());
    }

    public function getVisibilityOptions(): array
    {
        $options = [
            User::VISIBILITY_REGISTERED_ONLY => Yii::t('UserModule.account', 'Registered users only'),
        ];

        if (AuthHelper::isGuestAccessEnabled()) {
            $options[User::VISIBILITY_ALL] = Yii::t('UserModule.account', 'Visible for all (also unregistered users)');
        }

        if ($this->isHiddenUser() || Yii::$app->user->isAdmin()) {
            $options[User::VISIBILITY_HIDDEN] = Yii::t('AdminModule.user', 'Invisible');
        }

        return $options;
    }

    public function getEditorModeList(): array
    {
        return [
            User::EDITOR_RICH_TEXT => Yii::t('UserModule.account', 'Rich Text'),
            User::EDITOR_PLAIN => Yii::t('UserModule.account', 'Plain'),
        ];
    }
}
