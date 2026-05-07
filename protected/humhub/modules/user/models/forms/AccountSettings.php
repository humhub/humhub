<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use DateTimeZone;
use humhub\modules\dashboard\widgets\Sidebar as DashboardSidebar;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\BaseInflector;

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

    /**
     * Key = Panel ID
     * Value = Panel Label
     * @since 1.19
     */
    public $hiddenPanels = [];
    /**
     * Auto-Generated from $hiddenPanels
     */
    public $hiddenPanelIds = [];

    public $visibility;
    public $timeZone;
    public $blockedUsers;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tags', 'hiddenPanelIds', 'blockedUsers'], 'safe'],
            [['hideOnlineStatus'], 'boolean'],
            [['hiddenPanels'], 'each', 'rule' => ['string']],
            [['markdownEditorMode'], 'in', 'range' => [0, 1]],
            [['timeZone'], 'in', 'range' => DateTimeZone::listIdentifiers()],
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
            ['visibility', 'in', 'range' => array_keys($this->getVisibilityOptions()),
                'when' => fn(self $model) => $model->isVisibilityViewable() && $model->isVisibilityEditable()],
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
            'hiddenPanelIds' => Yii::t('UserModule.account', 'Hidden panels in the sidebar'),
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

    public static function isHiddenPanel(string $panelId): bool
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $hiddenPanels = (array) $user->settings->getSerialized('hiddenPanels');
        return array_key_exists($panelId, $hiddenPanels);
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
