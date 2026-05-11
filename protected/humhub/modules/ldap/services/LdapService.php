<?php

namespace humhub\modules\ldap\services;

use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\ldap\Module;
use LdapRecord\Connection;
use Yii;

/**
 * LdapService is the connection and query layer for a single LDAP connection.
 *
 * Stateless w.r.t. AuthClients or UserSources — receives all parameters via
 * LdapConnectionConfig. Obtain instances from {@see LdapConnectionRegistry}.
 *
 * @since 1.19 the constructor takes LdapConnectionConfig (was LdapAuth before)
 */
class LdapService
{
    public Connection $connection;

    public function __construct(public readonly LdapConnectionConfig $config)
    {
        $this->ldapConnect();
    }

    private function ldapConnect(): void
    {
        $this->connection = new Connection([
            'hosts' => [$this->config->hostname],
            'port' => $this->config->port,
            'username' => $this->config->bindUsername,
            'password' => $this->config->bindPassword,
            'base_dn' => $this->config->baseDn,
            'use_tls' => $this->config->useSsl,
            'use_starttls' => $this->config->useStartTls,
            'use_sasl' => false,
            'timeout' => $this->config->networkTimeout,
            'options' => [
                LDAP_OPT_X_TLS_REQUIRE_CERT => $this->config->disableCertificateChecking
                    ? LDAP_OPT_X_TLS_NEVER
                    : LDAP_OPT_X_TLS_DEMAND,
            ],
        ]);
        $this->connection->connect();
    }

    public function attemptAuth(string $username, string $password): ?string
    {
        $userDn = $this->getUserDn($username);
        if ($userDn === null || !$this->connection->auth()->attempt($userDn, $password)) {
            return null;
        }

        // Reconnect with the bind user after a successful user auth
        $this->ldapConnect();

        return $userDn;
    }

    public function countUsers(): int
    {
        $query = $this->connection->query()
            ->select('dn')
            ->rawFilter($this->config->userFilter);

        return count($query->paginate($this->getPageSize()));
    }

    public function getUserDn(string $usernameOrEmail): ?string
    {
        $result = $this->connection->query()
            ->select('dn')
            ->rawFilter($this->config->userFilter)
            ->orFilter(function ($query) use ($usernameOrEmail): void {
                $query->where($this->config->usernameAttribute, '=', $usernameOrEmail)
                    ->where($this->config->emailAttribute, '=', $usernameOrEmail);
            })
            ->first();

        return $result['dn'] ?? null;
    }

    /**
     * Returns all LDAP user entries (cleaned, without DN suppression).
     * Caller is responsible for normalisation into HumHub user attribute maps.
     *
     * @return array<array{dn: string, ...}> raw entries, one per user
     */
    public function getAllUserEntries(): array
    {
        $query = $this->connection->query()
            ->in($this->config->baseDn)
            ->rawFilter($this->config->userFilter)
            ->select($this->getQueriedAttributes());

        $entries = [];
        foreach ($query->paginate($this->getPageSize()) as $entity) {
            $dn = strtolower((string)$entity['dn']);
            foreach ($this->config->ignoredDNs as $ignoredDN) {
                if (!empty($ignoredDN) && str_starts_with($dn, strtolower($ignoredDN))) {
                    continue 2;
                }
            }

            $entries[] = LdapHelper::cleanLdapResponse($entity);
        }

        return $entries;
    }

    public function getEntry(string $dn, ?array $attributes = null): ?array
    {
        $attributes ??= $this->getQueriedAttributes();

        $result = $this->connection->query()
            ->select($attributes)
            ->setDn($dn)
            ->first();

        return $result !== null ? LdapHelper::cleanLdapResponse($result) : null;
    }

    public function getDnList(string $searchQuery): array
    {
        $results = [];
        $query = $this->connection->query()
            ->select('dn')
            ->rawFilter($searchQuery);

        foreach ($query->paginate($this->getPageSize()) as $entity) {
            $results[] = strtolower((string) $entity['dn']);
        }

        return $results;
    }

    private function getQueriedAttributes(): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $extra = $this->config->queriedAttributes ?? $module->queriedAttributes;

        return array_merge(['*', 'dn'], $extra);
    }

    private function getPageSize(): int
    {
        if ($this->config->pageSize !== null) {
            return $this->config->pageSize;
        }
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        return $module->pageSize;
    }
}
