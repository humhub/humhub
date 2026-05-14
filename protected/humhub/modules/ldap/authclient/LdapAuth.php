<?php

namespace humhub\modules\ldap\authclient;

use DateTime;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\user\authclient\BaseFormClient;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\ProfileField;
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
class LdapAuth extends BaseFormClient
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
     * Performs an LDAP bind and, on success, populates the user attributes
     * from the directory entry. Identity resolution against the HumHub
     * database lives downstream — see
     * {@see \humhub\modules\user\services\AuthClientService::getUser()} and
     * {@see \humhub\modules\ldap\source\LdapUserSource::findUser()}.
     */
    public function authenticate(string $username, string $password): bool
    {
        try {
            $service = $this->getLdapService();
            $dn = $service->attemptAuth($username, $password);
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
