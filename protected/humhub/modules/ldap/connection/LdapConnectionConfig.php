<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\connection;

use yii\base\BaseObject;

/**
 * LdapConnectionConfig holds all parameters required to establish and query
 * an LDAP connection. One instance per registered connection (e.g. 'ldap',
 * 'partner_corp', etc.) lives in the LdapConnectionRegistry.
 *
 * Plain value object — no connection state. Safe to serialize.
 *
 * @since 1.19
 */
class LdapConnectionConfig extends BaseObject
{
    /** Display title shown in the admin UI / on the user source */
    public string $title = 'LDAP';

    public string $hostname = '';
    public int $port = 389;
    public bool $useSsl = false;
    public bool $useStartTls = false;
    public bool $disableCertificateChecking = false;
    public int $networkTimeout = 30;

    public string $bindUsername = '';
    public string $bindPassword = '';

    public string $baseDn = '';
    public string $userFilter = '';

    public ?string $idAttribute = null;
    public string $usernameAttribute = 'samaccountname';
    public string $emailAttribute = 'mail';
    public string $languageAttribute = 'preferredLanguage';

    /** @var string[] lowercased DNs to skip on sync */
    public array $ignoredDNs = [];

    /** Attributes synced into the user table (Profile fields come from ProfileField) */
    public array $syncUserTableAttributes = ['username', 'email'];

    /** Whether LdapSyncJob should refresh users for this connection */
    public bool $autoRefreshUsers = false;

    /**
     * Page size for paginated LDAP queries. Null falls back to the
     * module-wide default ({@see Module::$pageSize}).
     */
    public ?int $pageSize = null;

    /**
     * Additional attributes to include in user queries (merged with the
     * defaults). Null falls back to the module-wide default
     * ({@see Module::$queriedAttributes}).
     *
     * @var string[]|null
     */
    public ?array $queriedAttributes = null;
}
