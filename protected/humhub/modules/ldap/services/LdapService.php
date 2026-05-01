<?php

namespace humhub\modules\ldap\services;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\ldap\Module;
use LdapRecord\Connection;
use Yii;
use yii\base\InvalidArgumentException;

class LdapService
{
    public Connection $connection;
    public readonly LdapAuth $authClient;

    public function __construct(LdapAuth $authClient)
    {
        $this->authClient = $authClient;
        $this->ldapConnect();
    }

    public static function create(string $authClientId = 'ldap'): self
    {
        /** @var LdapAuth $authClient */
        $authClient = Yii::$app->authClientCollection->getClient($authClientId);

        if (!$authClient instanceof LdapAuth) {
            throw new InvalidArgumentException("The specified ID does not match to a LDAP AuthClient");
        }

        return new self($authClient);
    }

    private function ldapConnect()
    {
        $this->connection = new Connection([
            'hosts' => [$this->authClient->hostname],
            'port' => $this->authClient->port,
            'username' => $this->authClient->bindUsername,
            'password' => $this->authClient->bindPassword,
            'base_dn' => $this->authClient->baseDn,
            'use_tls' => $this->authClient->useSsl,
            'use_starttls' => $this->authClient->useStartTls,
            'use_sasl' => false,
            'timeout' => $this->authClient->networkTimeout,
            'options' => [
                LDAP_OPT_X_TLS_REQUIRE_CERT => ($this->authClient->disableCertificateChecking)
                    ? LDAP_OPT_X_TLS_NEVER : LDAP_OPT_X_TLS_DEMAND,
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

        // Reconnect Ldap with Bind user
        $this->ldapConnect();

        return $userDn;
    }

    public function countUsers(): int
    {
        $query = $this->connection->query()->select('dn')
            ->rawFilter($this->authClient->userFilter);

        return count($query->paginate($this->getPageSize()));
    }

    public function getUserDn(string $usernameOrEmail): ?string
    {
        $result = $this->connection->query()
            ->select('dn')
            ->rawFilter($this->authClient->userFilter)
            ->orFilter(function ($query) use ($usernameOrEmail) {
                $query->where($this->authClient->usernameAttribute, '=', $usernameOrEmail)
                    ->where($this->authClient->emailAttribute, '=', $usernameOrEmail);
            })
            ->first();

        if (isset($result['dn'])) {
            return $result['dn'];
        }

        return null;
    }

    private function getAllUsersAttributes(): array
    {
        $query = $this->connection->query()
            ->in($this->authClient->baseDn)
            ->rawFilter($this->authClient->userFilter)
            ->select($this->getQueriedAttributes());

        $users = [];
        foreach ($query->paginate($this->getPageSize()) as $entity) {
            $dn = strtolower((string)$entity['dn']);
            foreach ($this->authClient->ignoredDNs as $ignoredDN) {
                if (!empty($ignoredDN) && str_starts_with($dn, strtolower($ignoredDN))) {
                    continue 2;
                }
            }

            $users[] = LdapHelper::cleanLdapResponse($entity);
        }

        return $users;
    }

    private function getQueriedAttributes(): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');

        return array_merge(['*', 'dn'], $module->queriedAttributes);
    }

    /**
     * @return LdapAuth[]
     */
    public function getAuthClients(): array
    {
        $authClients = [];

        foreach ($this->getAllUsersAttributes() as $ldapEntry) {
            $authClient = clone $this->authClient;
            $authClient->init();
            $authClient->setUserAttributes($ldapEntry);

            // Init
            $authClient->getUserAttributes();
            $authClient->ldapService = $this;

            $authClients[$ldapEntry['dn']] = $authClient;
        }

        return $authClients;
    }

    private function getPageSize(): int
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');

        return $module->pageSize;
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
        $query = $this->connection->query()->select('dn')
            ->rawFilter($searchQuery);

        foreach ($query->paginate($this->getPageSize()) as $entity) {
            $results[] = strtolower($entity['dn']);
        }

        return $results;
    }
}
