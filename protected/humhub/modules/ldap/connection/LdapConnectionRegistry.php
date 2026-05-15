<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\connection;

use humhub\modules\ldap\services\LdapService;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;

/**
 * LdapConnectionRegistry holds all configured LDAP connections keyed by ID.
 *
 * The 'ldap' default entry is populated from the DB-backed LdapSettings UI.
 * Additional connections may be added via the LDAP module config in
 * common.php (`modules.ldap.connections`) and are not editable in the UI.
 *
 * Connection IDs are also used as AuthClient IDs and UserSource IDs — the
 * registry is the single source of truth for the wiring.
 *
 * @since 1.19
 */
class LdapConnectionRegistry extends Component
{
    /** @var array<string, LdapConnectionConfig|array> raw configs keyed by ID */
    private array $configs = [];

    /** @var array<string, LdapService> instantiated services cache */
    private array $services = [];

    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    public function add(string $id, LdapConnectionConfig|array $config): void
    {
        $this->configs[$id] = $config;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->configs);
    }

    public function getIds(): array
    {
        return array_keys($this->configs);
    }

    public function getConfig(string $id): LdapConnectionConfig
    {
        if (!array_key_exists($id, $this->configs)) {
            throw new InvalidArgumentException("Unknown LDAP connection: '{$id}'.");
        }
        if (!$this->configs[$id] instanceof LdapConnectionConfig) {
            $this->configs[$id] = Yii::createObject(array_merge(
                ['class' => LdapConnectionConfig::class],
                $this->configs[$id],
            ));
        }
        return $this->configs[$id];
    }

    /**
     * Returns a connected LdapService for the given connection ID.
     * Service instances are cached per request.
     */
    public function getService(string $id): LdapService
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = new LdapService($this->getConfig($id));
        }
        return $this->services[$id];
    }

    /**
     * Drops any cached LdapService instances (e.g. before session serialization).
     */
    public function resetServices(): void
    {
        $this->services = [];
    }
}
