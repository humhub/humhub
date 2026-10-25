<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

use humhub\helpers\Html;
use humhub\libs\BasePermission;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\Module;
use humhub\modules\user\models\GroupPermission;
use Yii;
use yii\base\Model;

/**
 * The settings of "Apps & API Access" in the administration.
 *
 * @since 1.20
 */
class SettingsForm extends Model
{
    public $allowApp;
    public $appIds = '';
    public $allowClients;
    public $allowPersonalAccessTokens;
    public $patScopes = [];
    public $patMaxLifetimeDays;
    public $maxPersonalAccessTokensPerUser;
    public $refreshTokenLifetimeDays;
    public $grantMaxLifetimeDays;
    public $revokeOnPasswordChange;

    /**
     * @var bool whether the stored token scopes contain `full`, which the current user cannot change: it stays on save
     */
    private bool $lockedFullAccess = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $module = Module::instance();
        $this->allowApp = $module->allowsApp();
        $this->appIds = implode("\n", $module->getAdditionalAppIds());
        $this->allowClients = $module->allowsClients();
        $this->allowPersonalAccessTokens = $module->allowsPersonalAccessTokens();
        $this->patScopes = $this->onlyRegistered($module->getPatScopes());
        if (!$this->canSetFullAccess() && in_array(ScopeRegistry::FULL, $this->patScopes, true)) {
            $this->lockedFullAccess = true;
            $this->patScopes = array_values(array_diff($this->patScopes, [ScopeRegistry::FULL]));
        }
        $this->patMaxLifetimeDays = $module->getPatMaxLifetimeDays();
        $this->maxPersonalAccessTokensPerUser = $module->getMaxPersonalAccessTokensPerUser();
        $this->refreshTokenLifetimeDays = $module->getRefreshTokenLifetimeDays();
        $this->grantMaxLifetimeDays = $module->getGrantMaxLifetimeDays();
        $this->revokeOnPasswordChange = $module->revokesOnPasswordChange();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $scopeIds = array_keys(ScopeRegistry::getScopes());

        return [
            [['allowApp', 'allowClients', 'allowPersonalAccessTokens', 'revokeOnPasswordChange'], 'boolean'],
            ['patMaxLifetimeDays', 'integer', 'min' => 0],
            ['refreshTokenLifetimeDays', 'integer', 'min' => 1],
            ['grantMaxLifetimeDays', 'integer', 'min' => 0],
            ['maxPersonalAccessTokensPerUser', 'integer', 'min' => 0],
            ['maxPersonalAccessTokensPerUser', 'required'],
            ['maxPersonalAccessTokensPerUser', 'validateMaxPersonalAccessTokensPerUser'],
            [['patMaxLifetimeDays', 'refreshTokenLifetimeDays', 'grantMaxLifetimeDays'], 'required'],
            ['patScopes', 'each', 'rule' => ['in', 'range' => $scopeIds]],
            ['patScopes', 'validatePatScopes'],
            ['appIds', 'validateAppIds'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // A multi select without a selection is not submitted at all
        if (!is_array($this->patScopes)) {
            $this->patScopes = [];
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'allowApp' => Yii::t('OauthModule.base', 'Allow the HumHub app to sign in'),
            'appIds' => Yii::t('OauthModule.base', 'Additional app IDs (one per line)'),
            'allowClients' => Yii::t('OauthModule.base', 'Allow clients created by administrators'),
            'allowPersonalAccessTokens' => Yii::t('OauthModule.base', 'Allow personal access tokens'),
            'patScopes' => Yii::t('OauthModule.base', 'Scopes available to personal access tokens'),
            'patMaxLifetimeDays' => Yii::t('OauthModule.base', 'Maximum lifetime of personal access tokens (days)'),
            'maxPersonalAccessTokensPerUser' => Yii::t('OauthModule.base', 'Maximum personal access tokens per user'),
            'refreshTokenLifetimeDays' => Yii::t('OauthModule.base', 'Refresh token lifetime (days)'),
            'grantMaxLifetimeDays' => Yii::t('OauthModule.base', 'Maximum lifetime of a grant (days)'),
            'revokeOnPasswordChange' => Yii::t('OauthModule.base', 'Revoke access when the password changes'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        $off = Yii::t('OauthModule.base', 'Switching this off also stops all existing sign-ins and tokens of this kind immediately; switching it on again restores them.');

        return [
            'allowApp' => $off,
            // Hints are not encoded
            'appIds' => Html::encode(Yii::t('OauthModule.base', 'Customer-specific apps that sign in as the HumHub app. Each app signs in with the redirect URI <app ID>:/oauth/callback.')),
            'allowClients' => $off,
            'allowPersonalAccessTokens' => $off . ' ' . Yii::t('OauthModule.base', 'Users additionally need the group permission "Create personal access tokens".'),
            'patScopes' => Yii::t('OauthModule.base', 'The upper bound of what a personal access token can do. An empty selection means no access.'),
            'patMaxLifetimeDays' => Yii::t('OauthModule.base', '0 means unlimited and offers "never" as a lifetime.'),
            'maxPersonalAccessTokensPerUser' => Yii::t('OauthModule.base', '0 means unlimited. Expired tokens do not count.'),
            'refreshTokenLifetimeDays' => Yii::t('OauthModule.base', 'Idle lifetime: renewed on every refresh, so an app in regular use stays signed in.'),
            'grantMaxLifetimeDays' => Yii::t('OauthModule.base', '0 means unlimited.'),
            'revokeOnPasswordChange' => Yii::t('OauthModule.base', 'A password change deletes all grants of the user except the app session the change was made in.'),
        ];
    }

    /**
     * Whether the additional app IDs can be changed here: only by system administrators, and not when set in the configuration.
     */
    public function canEditAppIds(): bool
    {
        return !Module::instance()->isAppIdsConfigured()
            && (bool)Yii::$app->user->getIdentity()?->isSystemAdmin();
    }

    /**
     * Whether `full` can be added to or removed from the token scopes: only by system administrators.
     */
    public function canSetFullAccess(): bool
    {
        return (bool)Yii::$app->user->getIdentity()?->isSystemAdmin();
    }

    /**
     * Whether the token scopes contain `full`, which the current user cannot change.
     */
    public function hasLockedFullAccess(): bool
    {
        return $this->lockedFullAccess;
    }

    public function validatePatScopes(string $attribute): void
    {
        if (!$this->canSetFullAccess() && in_array(ScopeRegistry::FULL, $this->$attribute, true)) {
            $this->addError($attribute, Yii::t('OauthModule.base', 'Only system administrators can change this.'));
        }
    }

    public function validateMaxPersonalAccessTokensPerUser(string $attribute): void
    {
        if (Module::instance()->isMaxPersonalAccessTokensPerUserConfigured()
            && (int)$this->$attribute !== Module::instance()->getMaxPersonalAccessTokensPerUser()) {
            $this->addError($attribute, Yii::t('OauthModule.base', 'This is set in the configuration file.'));
        }
    }

    public function validateAppIds(string $attribute): void
    {
        if (!is_string($this->appIds)) {
            $this->addError($attribute, Yii::t('OauthModule.base', 'Invalid input.'));
            return;
        }

        if (!$this->canEditAppIds()) {
            if ($this->getAppIdList() !== Module::instance()->getAdditionalAppIds()) {
                $this->addError($attribute, Module::instance()->isAppIdsConfigured()
                    ? Yii::t('OauthModule.base', 'This is set in the configuration file.')
                    : Yii::t('OauthModule.base', 'Only system administrators can change this.'));
            }
            return;
        }

        foreach ($this->getAppIdList() as $appId) {
            if (!Module::isValidAppId($appId)) {
                $this->addError($attribute, Yii::t('OauthModule.base', 'Not a valid app ID: {id}', ['id' => $appId]));
            }
        }
    }

    /**
     * @return string[] the entered app IDs, normalized, without the built-in one
     */
    public function getAppIdList(): array
    {
        if (!is_string($this->appIds)) {
            return [];
        }

        $ids = array_filter(array_map([Module::class, 'normalizeAppId'], preg_split('/[\s,]+/', $this->appIds)));

        return array_values(array_unique(array_diff($ids, [Module::BUILT_IN_APP_ID])));
    }

    /**
     * @return array<string, string> scope labels by id
     */
    public function getScopeOptions(): array
    {
        $options = [];
        foreach (ScopeRegistry::getScopes() as $scope) {
            if ($scope->id === ScopeRegistry::FULL && !$this->canSetFullAccess()) {
                continue;
            }
            $options[$scope->id] = $scope->label;
        }

        return $options;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $settings = Module::instance()->settings;
        if ($this->canEditAppIds()) {
            $appIds = $this->getAppIdList();
            if ($appIds === []) {
                $settings->delete('appIds');
            } else {
                $settings->set('appIds', json_encode($appIds));
            }
        }
        foreach (['allowApp', 'allowClients', 'allowPersonalAccessTokens', 'revokeOnPasswordChange'] as $name) {
            $settings->set($name, (bool)$this->$name);
        }
        foreach (['patMaxLifetimeDays', 'refreshTokenLifetimeDays', 'grantMaxLifetimeDays'] as $name) {
            $settings->set($name, (int)$this->$name);
        }
        if (!Module::instance()->isMaxPersonalAccessTokensPerUserConfigured()) {
            $settings->set('maxPersonalAccessTokensPerUser', (int)$this->maxPersonalAccessTokensPerUser);
        }
        $selection = $this->patScopes;
        // `full` set by a system administrator stays when someone else saves
        if ($this->lockedFullAccess) {
            $selection[] = ScopeRegistry::FULL;
        }
        $selection = array_values(array_unique($selection));
        $default = ScopeRegistry::getDefaultRestrictedScopeIds();
        sort($selection);
        sort($default);

        // The default stays unset, so newly registered core read scopes keep being included
        if ($selection === $default) {
            $settings->delete('patScopes');
        } else {
            $settings->set('patScopes', json_encode($selection));
        }

        return true;
    }

    /**
     * A stored id of a scope that is no longer registered (disabled module) must not block saving.
     */
    private function onlyRegistered(array $scopeIds): array
    {
        return array_values(array_filter($scopeIds, static fn(string $id) => ScopeRegistry::has($id)));
    }

    /**
     * Whether any group is allowed the permission — without one, an enabled feature is
     * invisible to every user.
     *
     * @param class-string<BasePermission> $permissionClass
     */
    public function isGrantedToAnyGroup(string $permissionClass): bool
    {
        return GroupPermission::find()
            ->where(['permission_id' => (new $permissionClass())->getId(), 'state' => BasePermission::STATE_ALLOW])
            ->exists();
    }
}
