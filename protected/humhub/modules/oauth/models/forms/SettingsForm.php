<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\Module;
use Yii;
use yii\base\Model;

/**
 * The settings of "Apps & API Access" in the administration.
 *
 * @since 1.20
 */
class SettingsForm extends Model
{
    /**
     * Never grantable to user clients.
     */
    private const SCOPE_FULL = 'full';

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
        $this->userClientScopes = $module->getUserClientScopes();
        $this->allowPersonalAccessTokens = $module->allowsPersonalAccessTokens();
        $this->patScopes = $module->getPatScopes();
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
            ['userClientScopes', 'each', 'rule' => ['in', 'range' => array_values(array_diff($scopeIds, [self::SCOPE_FULL]))]],
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
        return [
            'allowUserClients' => Yii::t('OauthModule.base', 'Creating clients requires the group permission "Manage own OAuth clients".'),
            'userClientScopes' => Yii::t('OauthModule.base', 'The upper bound of what a user client can request.'),
            'allowPersonalAccessTokens' => Yii::t('OauthModule.base', 'Creating tokens requires the group permission "Create personal access tokens".'),
            'patScopes' => Yii::t('OauthModule.base', 'The upper bound of what a personal access token can do.'),
            'patMaxLifetimeDays' => Yii::t('OauthModule.base', '0 means unlimited and offers "never" as a lifetime.'),
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
            if ($includeFull || $scope->id !== self::SCOPE_FULL) {
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
            $settings->set($name, json_encode(array_values(array_unique($this->$name))));
        }

        return true;
    }
}
