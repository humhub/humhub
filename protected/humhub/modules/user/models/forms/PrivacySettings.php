<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\models\UrlOembed;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\base\Model;

/**
 * Form Model for the privacy settings of the user's account
 *
 * @since 1.20
 */
class PrivacySettings extends Model
{
    public $visibility;
    public $hideOnlineStatus;
    public $blockedUsers;

    /**
     * @var array|string|null Domains always allowed to display external content (oEmbed)
     */
    public $allowedExternalSources;

    public ?User $user = null;

    /**
     * @var string[] the domains allowed before the submit, only these can be kept
     */
    private array $allowedDomains = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->user ??= Yii::$app->user->getIdentity();
        $this->visibility = $this->user->visibility;
        $this->hideOnlineStatus = $this->user->settings->get('hideOnlineStatus');
        $this->blockedUsers = $this->user->getBlockedUserGuids();
        $this->allowedDomains = UrlOembed::getAllowedDomains();
        $this->allowedExternalSources = $this->allowedDomains;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['blockedUsers', 'allowedExternalSources'], 'safe'],
            [['hideOnlineStatus'], 'boolean'],
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
            'visibility' => Yii::t('UserModule.account', 'Profile visibility'),
            'hideOnlineStatus' => Yii::t('UserModule.account', 'Hide my online status'),
            'blockedUsers' => Yii::t('UserModule.account', 'Blocked users'),
            'allowedExternalSources' => Yii::t('UserModule.account', 'Allowed external content sources'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'allowedExternalSources' => Yii::t('UserModule.account', 'Content from these sources is displayed without asking for confirmation. Remove a source to be asked again.'),
        ];
    }

    /**
     * @return bool whether at least one of the settings is shown
     */
    public function hasFields(): bool
    {
        return $this->isVisibilityViewable()
            || $this->isOnlineStatusEnabled()
            || $this->getAllowedDomains() !== []
            || $this->isBlockUsersEnabled();
    }

    public function isHiddenUser(): bool
    {
        return $this->user->visibility == User::VISIBILITY_HIDDEN;
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

    public function isOnlineStatusEnabled(): bool
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('user');

        return !$module->settings->get('auth.hideOnlineStatus');
    }

    public function isBlockUsersEnabled(): bool
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('user');

        return $module->allowBlockUsers();
    }

    /**
     * @return string[] the domains the user currently allows to display external content
     */
    public function getAllowedDomains(): array
    {
        return $this->allowedDomains;
    }

    /**
     * @return string[]
     */
    public function getAllowedExternalSources(): array
    {
        return is_array($this->allowedExternalSources)
            ? array_values(array_filter($this->allowedExternalSources, 'is_string'))
            : [];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->user->settings->set('hideOnlineStatus', $this->hideOnlineStatus);

        $this->user->scenario = User::SCENARIO_EDIT_ACCOUNT_SETTINGS;
        // A crafted POST must not change a visibility the user may not edit, e.g. un-hide a hidden user
        if ($this->isVisibilityViewable() && $this->isVisibilityEditable()) {
            $this->user->visibility = $this->visibility;
        }
        if ($this->isBlockUsersEnabled()) {
            $this->user->blockedUsersField = $this->blockedUsers;
        }
        $this->user->save();

        if ($this->allowedDomains !== []) {
            UrlOembed::restrictAllowedDomains($this->getAllowedExternalSources());
        }

        return true;
    }
}
