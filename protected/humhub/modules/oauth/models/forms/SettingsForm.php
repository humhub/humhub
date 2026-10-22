<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

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
    public $allowClients;
    public $allowUserClients;
    public $userClientScopes = [];
    public $allowPersonalAccessTokens;
    public $patScopes = [];
    public $patMaxLifetimeDays;
    public $refreshTokenLifetimeDays;
    public $grantMaxLifetimeDays;
    public $revokeOnPasswordChange;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $module = Module::instance();
        $this->allowApp = $module->allowsApp();
        $this->allowClients = $module->allowsClients();
        $this->allowUserClients = $module->allowsUserClients();
        $this->userClientScopes = $this->onlyRegistered($module->getUserClientScopes());
        $this->allowPersonalAccessTokens = $module->allowsPersonalAccessTokens();
        $this->patScopes = $this->onlyRegistered($module->getPatScopes());
        $this->patMaxLifetimeDays = $module->getPatMaxLifetimeDays();
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
            [['allowApp', 'allowClients', 'allowUserClients', 'allowPersonalAccessTokens', 'revokeOnPasswordChange'], 'boolean'],
            ['patMaxLifetimeDays', 'integer', 'min' => 0],
            ['refreshTokenLifetimeDays', 'integer', 'min' => 1],
            ['grantMaxLifetimeDays', 'integer', 'min' => 0],
            [['patMaxLifetimeDays', 'refreshTokenLifetimeDays', 'grantMaxLifetimeDays'], 'required'],
            ['patScopes', 'each', 'rule' => ['in', 'range' => $scopeIds]],
            ['userClientScopes', 'each', 'rule' => ['in', 'range' => array_values(array_diff($scopeIds, [ScopeRegistry::FULL]))]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // A multi select without a selection is not submitted at all
        foreach (['patScopes', 'userClientScopes'] as $attribute) {
            if (!is_array($this->$attribute)) {
                $this->$attribute = [];
            }
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
            'allowClients' => Yii::t('OauthModule.base', 'Allow clients created by administrators'),
            'allowUserClients' => Yii::t('OauthModule.base', 'Allow clients created by users'),
            'userClientScopes' => Yii::t('OauthModule.base', 'Scopes available to user clients'),
            'allowPersonalAccessTokens' => Yii::t('OauthModule.base', 'Allow personal access tokens'),
            'patScopes' => Yii::t('OauthModule.base', 'Scopes available to personal access tokens'),
            'patMaxLifetimeDays' => Yii::t('OauthModule.base', 'Maximum lifetime of personal access tokens (days)'),
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
            'allowClients' => $off,
            'allowUserClients' => $off . ' ' . Yii::t('OauthModule.base', 'Users additionally need the group permission "Register own OAuth apps".'),
            'userClientScopes' => Yii::t('OauthModule.base', 'The upper bound of what a user client can request. An empty selection means no access.'),
            'allowPersonalAccessTokens' => $off . ' ' . Yii::t('OauthModule.base', 'Users additionally need the group permission "Create personal access tokens".'),
            'patScopes' => Yii::t('OauthModule.base', 'The upper bound of what a personal access token can do. An empty selection means no access.'),
            'patMaxLifetimeDays' => Yii::t('OauthModule.base', '0 means unlimited and offers "never" as a lifetime.'),
            'refreshTokenLifetimeDays' => Yii::t('OauthModule.base', 'Idle lifetime: renewed on every refresh, so an app in regular use stays signed in.'),
            'grantMaxLifetimeDays' => Yii::t('OauthModule.base', '0 means unlimited.'),
            'revokeOnPasswordChange' => Yii::t('OauthModule.base', 'A password change deletes all grants of the user except the app session the change was made in.'),
        ];
    }

    /**
     * @return array<string, string> scope labels by id; with or without `full`
     */
    public function getScopeOptions(bool $includeFull = true): array
    {
        $options = [];
        foreach (ScopeRegistry::getScopes() as $scope) {
            if ($includeFull || $scope->id !== ScopeRegistry::FULL) {
                $options[$scope->id] = $scope->label;
            }
        }

        return $options;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $settings = Module::instance()->settings;
        foreach (['allowApp', 'allowClients', 'allowUserClients', 'allowPersonalAccessTokens', 'revokeOnPasswordChange'] as $name) {
            $settings->set($name, (bool)$this->$name);
        }
        foreach (['patMaxLifetimeDays', 'refreshTokenLifetimeDays', 'grantMaxLifetimeDays'] as $name) {
            $settings->set($name, (int)$this->$name);
        }
        foreach (['patScopes', 'userClientScopes'] as $name) {
            $selection = array_values(array_unique($this->$name));
            $default = ScopeRegistry::getDefaultRestrictedScopeIds();
            sort($selection);
            sort($default);

            // The default stays unset, so newly registered core read scopes keep being included
            if ($selection === $default) {
                $settings->delete($name);
            } else {
                $settings->set($name, json_encode($selection));
            }
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
