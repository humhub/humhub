<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap;

use humhub\modules\ldap\connection\LdapConnectionRegistry;
use humhub\modules\ldap\models\LdapSettings;
use Yii;

class Module extends \humhub\components\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\ldap\controllers';

    /**
     * @var int Default page size for paginated LDAP queries (0 disables pagination).
     * Per-connection override: {@see LdapConnectionConfig::$pageSize}.
     */
    public $pageSize = 1000;

    /**
     * @var array Default list of extra attributes to query (merged with `*` and `dn`).
     * Per-connection override: {@see LdapConnectionConfig::$queriedAttributes}.
     */
    public $queriedAttributes = [];

    /**
     * @var array additional LDAP connection configurations keyed by ID.
     * Populated via the module config in `common.php` (`modules.ldap.connections`).
     * The default 'ldap' connection is loaded from {@see LdapSettings} (DB UI)
     * and merged in by {@see getConnectionRegistry()}.
     * @since 1.19
     */
    public array $connections = [];

    private ?LdapConnectionRegistry $_registry = null;

    /**
     * Returns the LDAP connection registry, initialised lazily from
     * LdapSettings (default 'ldap' connection) plus any configs added via
     * {@see $connections}.
     *
     * @since 1.19
     */
    public function getConnectionRegistry(): LdapConnectionRegistry
    {
        if ($this->_registry === null) {
            $configs = $this->connections;

            if (LdapSettings::isEnabled()) {
                $settings = new LdapSettings();
                $settings->loadSaved();
                $configs = array_merge(
                    ['ldap' => $settings->getConnectionConfig()],
                    $configs,
                );
            }

            $this->_registry = new LdapConnectionRegistry();
            $this->_registry->setConfigs($configs);
        }
        return $this->_registry;
    }

    /**
     * Replaces the connection registry — primarily used in tests.
     * @internal
     */
    public function setConnectionRegistry(LdapConnectionRegistry $registry): void
    {
        $this->_registry = $registry;
    }
}
