<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Creating and editing an OAuth client — by an administrator (`admin` scenario) or by a user
 * for their own account (`user` scenario).
 *
 * @since 1.20
 */
class ClientForm extends Model
{
    public const SCENARIO_ADMIN = 'admin';
    public const SCENARIO_USER = 'user';

    public $name;
    public $description;
    public $confidential = false;
    public $redirectUris = '';
    public $scopes = [];
    public $skipConsent = false;
    public $isActive = true;

    private ?string $secret = null;

    public function __construct(public readonly Client $client, private readonly User $actor, $config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        if (!$this->client->isNewRecord) {
            $this->name = $this->client->name;
            $this->description = $this->client->description;
            $this->confidential = $this->client->isConfidential();
            $this->redirectUris = implode("\n", $this->client->getRedirectUris());
            // Scopes no longer offered would block saving
            $this->scopes = array_values(array_intersect($this->client->getAllowedScopes(), array_keys($this->getScopeOptions())));
            $this->skipConsent = (bool)$this->client->skip_consent;
            $this->isActive = (bool)$this->client->is_active;
        }
    }

    public function scenarios()
    {
        $common = ['name', 'description', 'redirectUris', 'scopes', 'confidential'];

        return [
            self::SCENARIO_ADMIN => [...$common, 'skipConsent', 'isActive'],
            self::SCENARIO_USER => $common,
        ];
    }

    public function rules()
    {
        return [
            [['name', 'redirectUris', 'scopes'], 'required'],
            ['name', 'string', 'max' => 100],
            ['description', 'string', 'max' => 1000],
            [['confidential', 'skipConsent', 'isActive'], 'boolean'],
            ['scopes', 'each', 'rule' => ['in', 'range' => array_keys($this->getScopeOptions())]],
            ['redirectUris', 'validateRedirectUris'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('OauthModule.base', 'Name'),
            'description' => Yii::t('OauthModule.base', 'Description'),
            'confidential' => Yii::t('OauthModule.base', 'Confidential client (has a secret, e.g. a server application)'),
            'redirectUris' => Yii::t('OauthModule.base', 'Redirect URIs (one per line)'),
            'scopes' => Yii::t('OauthModule.base', 'Allowed access'),
            'skipConsent' => Yii::t('OauthModule.base', 'Trusted: skip the consent page'),
            'isActive' => Yii::t('OauthModule.base', 'Active'),
        ];
    }

    /**
     * @return array<string, string> scope id => label, as allowed in the current scenario
     */
    public function getScopeOptions(): array
    {
        $ids = $this->scenario === self::SCENARIO_ADMIN
            ? array_keys(ScopeRegistry::getScopes())
            : array_diff(Module::instance()->getUserClientScopes(), [ScopeRegistry::FULL]);

        $options = [];
        foreach ($ids as $id) {
            if ($scope = ScopeRegistry::get($id)) {
                $options[$id] = $scope->label;
            }
        }

        return $options;
    }

    public function validateRedirectUris(string $attribute): void
    {
        if (!is_string($this->redirectUris) || $this->getRedirectUriList() === []) {
            $this->addError($attribute, Yii::t('OauthModule.base', 'Enter at least one redirect URI.'));
            return;
        }

        foreach ($this->getRedirectUriList() as $uri) {
            if (!self::isAllowedRedirectUri($uri, (bool)$this->confidential)) {
                $this->addError($attribute, Yii::t('OauthModule.base', 'Not allowed: {uri}', ['uri' => $uri]));
            }
        }
    }

    /**
     * https, loopback http (any port) and, for public clients, reverse-domain private-use schemes.
     */
    public static function isAllowedRedirectUri(string $uri, bool $confidential): bool
    {
        if (preg_match('/[\s\x00-\x1F\x7F]/', $uri) || str_contains($uri, '#')) {
            return false;
        }

        $parts = parse_url($uri);
        if ($parts === false || empty($parts['scheme']) || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        if ($scheme === 'https') {
            return self::isValidHost($parts['host'] ?? '');
        }
        if ($scheme === 'http') {
            // parse_url() keeps the brackets of IPv6 hosts
            return in_array($parts['host'] ?? '', ['127.0.0.1', '[::1]'], true);
        }

        // Private-use scheme in reverse-domain form, e.g. com.example.app:/callback
        return !$confidential
            && preg_match('/^[a-z][a-z0-9-]*(\.[a-z0-9-]+)+$/', $scheme) === 1
            && (!isset($parts['host']) || self::isValidHost($parts['host']));
    }

    private static function isValidHost(string $host): bool
    {
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }

        return $host !== ''
            && (filter_var($host, FILTER_VALIDATE_IP) !== false
                || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false);
    }

    /**
     * @return string[]
     */
    public function getRedirectUriList(): array
    {
        if (!is_string($this->redirectUris)) {
            return [];
        }

        $uris = array_filter(array_map('trim', preg_split('/\R/', $this->redirectUris)));

        // The scheme is case-insensitive; the exact match at the authorization endpoint is not
        $uris = array_map(
            static fn(string $uri) => preg_replace_callback('/^[A-Za-z][A-Za-z0-9+.-]*:/', static fn(array $m) => strtolower($m[0]), $uri),
            $uris,
        );

        return array_values(array_unique($uris));
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $isAdmin = $this->scenario === self::SCENARIO_ADMIN;
        $client = $this->client;
        if ($client->isNewRecord) {
            $client->client_id = bin2hex(random_bytes(16));
            $client->type = $isAdmin ? Client::TYPE_ADMIN : Client::TYPE_USER;
            $client->owner_user_id = $isAdmin ? null : $this->actor->id;
            $client->created_by = $this->actor->id;
            $client->created_at = time();
            $client->skip_consent = 0;
            $client->is_active = 1;
        }

        $client->name = $this->name;
        $client->description = $this->description ?: null;
        $client->redirect_uris = json_encode($this->getRedirectUriList());
        $client->scopes = json_encode(array_values((array)$this->scopes));

        // Users must not override what an administrator set for their client
        if ($isAdmin) {
            $client->skip_consent = $this->skipConsent ? 1 : 0;
            $client->is_active = $this->isActive ? 1 : 0;
        }

        if ($this->confidential && $client->secret_hash === null) {
            $this->generateSecret();
        } elseif (!$this->confidential) {
            $client->secret_hash = null;
        }

        return $client->save(false);
    }

    /**
     * Sets a new secret; it is readable once via [[getSecret()]]. The caller saves the client.
     */
    public function generateSecret(): void
    {
        $this->secret = Yii::$app->security->generateRandomString(48);
        $this->client->secret_hash = hash('sha256', $this->secret);
    }

    /**
     * @return string|null the secret set by this form, null if none was set
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }
}
