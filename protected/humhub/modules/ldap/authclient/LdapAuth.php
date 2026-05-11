<?php

namespace humhub\modules\ldap\authclient;

use DateTime;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * LDAP Authentication client.
 *
 * Dumb wrapper around an LDAP connection that the registry owns. Connection
 * parameters live on {@see LdapConnectionConfig}; this class only resolves
 * its connection by ID and handles auth + attribute normalisation.
 *
 * @since 1.1
 * @since 1.19 — connection parameters moved to LdapConnectionConfig / LdapConnectionRegistry.
 */
class LdapAuth extends BaseFormAuth
{
    /**
     * @var string|null ID of the connection this client uses. Required — must
     * match an entry in {@see LdapConnectionRegistry}. Also serves as the
     * AuthClient ID by default.
     */
    public ?string $connectionId = null;

    /**
     * @var string|null AuthClient ID — defaults to {@see $connectionId}.
     */
    public $clientId = null;

    public function init()
    {
        parent::init();

        if ($this->connectionId === null || $this->connectionId === '') {
            throw new InvalidConfigException(self::class . ' requires a non-empty $connectionId.');
        }
        if ($this->clientId === null || $this->clientId === '') {
            $this->clientId = $this->connectionId;
        }
    }

    public function getId()
    {
        return $this->clientId;
    }

    public function getConfig(): LdapConnectionConfig
    {
        return $this->getRegistry()->getConfig($this->connectionId);
    }

    public function getLdapService(): LdapService
    {
        return $this->getRegistry()->getService($this->connectionId);
    }

    private function getRegistry()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        return $module->getConnectionRegistry();
    }

    protected function defaultName()
    {
        return $this->clientId;
    }

    protected function defaultTitle()
    {
        return $this->getConfig()->title;
    }

    public function getIdAttribute()
    {
        $idAttribute = $this->getConfig()->idAttribute;
        return $idAttribute !== null ? strtolower($idAttribute) : null;
    }

    /**
     * Find user based on LDAP attributes.
     *
     * Primary lookup uses the user_auth table (source + source_id).
     * Falls back to matching by user_source + email/objectguid/username for
     * users without an idAttribute or not yet migrated.
     */
    public function getUser(): ?User
    {
        $attributes = $this->getUserAttributes();

        // Primary lookup: user_auth table
        if (isset($attributes['id'])) {
            $auth = Auth::find()
                ->where(['source' => $this->getId(), 'source_id' => (string)$attributes['id']])
                ->one();
            if ($auth !== null) {
                return $auth->user;
            }
        }

        // Fallback: match by user_source + email/objectguid/username
        return $this->getUserFallback($attributes);
    }

    /**
     * Fallback user lookup for users without a unique id attribute or legacy installs
     * where user_auth entries may not yet exist.
     */
    private function getUserFallback(array $attributes): ?User
    {
        $query = User::find()->where(['user_source' => $this->getId()]);

        $conditions = ['OR'];
        if (!empty($attributes['email'])) {
            $conditions[] = ['email' => $attributes['email']];
        }
        if (!empty($attributes['objectguid'])) {
            $conditions[] = ['guid' => $attributes['objectguid']];
        }
        if (!empty($attributes['uid'])) {
            $conditions[] = ['username' => $attributes['uid']];
        }

        if (count($conditions) <= 1) {
            return null;
        }

        return $query->andWhere($conditions)->one();
    }

    public function auth()
    {
        try {
            $service = $this->getLdapService();
            $dn = $service->attemptAuth($this->login->username, $this->login->password);
        } catch (\Exception $e) {
            Yii::error('LDAP authentication error: ' . $e->getMessage(), 'ldap');
            return false;
        }

        if ($dn === null) {
            if ($this->login instanceof Login) {
                $this->countFailedLoginAttempts();
            }
            return false;
        }

        $this->setUserAttributes($service->getEntry($dn));
        return true;
    }

    protected function defaultNormalizeUserAttributeMap()
    {
        $config = $this->getConfig();
        $map = [
            'username' => strtolower($config->usernameAttribute),
            'email' => strtolower($config->emailAttribute),
            'language' => strtolower($config->languageAttribute),
        ];

        foreach (ProfileField::find()->andWhere(['!=', 'ldap_attribute', ''])->all() as $profileField) {
            $map[$profileField->internal_name] = strtolower($profileField->ldap_attribute);
        }

        return $map;
    }

    protected function normalizeUserAttributes($attributes)
    {
        $normalized = LdapHelper::dropMultiValues($attributes, ['memberof', 'ismemberof']);

        // Handle date fields (formats are specified in config)
        foreach ($normalized as $name => $value) {
            if (isset(Yii::$app->params['ldap']['dateFields'][$name]) && $value != '') {
                $dateFormat = Yii::$app->params['ldap']['dateFields'][$name];
                $date = DateTime::createFromFormat($dateFormat, $value ?? '');

                $normalized[$name] = $date !== false ? $date->format('Y-m-d') : '';
            }
        }

        $idAttribute = $this->getIdAttribute();
        if ($idAttribute !== null && isset($normalized[$idAttribute])) {
            $normalized['id'] = $normalized[$idAttribute];
        }

        return parent::normalizeUserAttributes($normalized);
    }

    public function getUserAttributes()
    {
        $attributes = parent::getUserAttributes();

        $idAttribute = $this->getIdAttribute();
        if ($idAttribute !== null && isset($attributes[$idAttribute])) {
            $attributes['id'] = $attributes[$idAttribute];
        }

        return $attributes;
    }

    public function setNormalizeUserAttributeMap($normalizeUserAttributeMap)
    {
        // Merge HumHub auto mapping with config-provided overrides
        parent::setNormalizeUserAttributeMap(
            ArrayHelper::merge($this->defaultNormalizeUserAttributeMap(), $normalizeUserAttributeMap),
        );
    }
}
