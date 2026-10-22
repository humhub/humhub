<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth;

use humhub\modules\oauth\components\KeyStore;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Grant;
use Yii;
use yii\helpers\Url;

/**
 * OAuth authorization server and personal access tokens, see `docs/develop/concept-oauth.md`.
 *
 * @since 1.20
 */
class Module extends \humhub\components\Module
{
    /**
     * @var string|null the private key (PEM or `file://` path), overrides the stored key
     */
    public ?string $privateKey = null;

    /**
     * @var string|null the public key (PEM or `file://` path), overrides the stored key
     */
    public ?string $publicKey = null;

    /**
     * @var string|null the defuse encryption key (ASCII-safe string or `file://` path), overrides the stored key
     */
    public ?string $encryptionKey = null;

    /**
     * @var int lifetime of an access token in seconds
     */
    public int $accessTokenLifetime = 3600;

    /**
     * @var int lifetime of an authorization code in seconds
     */
    public int $authCodeLifetime = 600;

    /**
     * @var Grant|null the grant that authenticated the current request, set by `TokenAuth`
     */
    public ?Grant $activeGrant = null;

    private ?KeyStore $keyStore = null;

    public static function instance(): self
    {
        /** @var self $module */
        $module = Yii::$app->getModule('oauth');
        return $module;
    }

    public function getKeyStore(): KeyStore
    {
        return $this->keyStore ??= new KeyStore($this);
    }

    /**
     * The issuer: the configured base URL, falling back to the request's.
     */
    public function getIssuer(): string
    {
        $baseUrl = (string)Yii::$app->settings->get('baseUrl');
        return rtrim($baseUrl !== '' ? $baseUrl : Url::base(true), '/');
    }

    /**
     * An endpoint URL on the issuer's host (the metadata must be consistent with `issuer`).
     * `Url::to()` already contains the base path, so only scheme, host and port are taken from the issuer.
     */
    public function getEndpointUrl(array $route): string
    {
        $parts = parse_url($this->getIssuer());
        $origin = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');

        return $origin . Url::to($route);
    }

    public function allowsApp(): bool
    {
        return (bool)$this->settings->get('allowApp', true);
    }

    public function allowsClients(): bool
    {
        return (bool)$this->settings->get('allowClients', false);
    }

    public function allowsUserClients(): bool
    {
        return (bool)$this->settings->get('allowUserClients', false);
    }

    public function allowsPersonalAccessTokens(): bool
    {
        return (bool)$this->settings->get('allowPersonalAccessTokens', false);
    }

    /**
     * @return string[] the upper bound of scopes for user clients
     */
    public function getUserClientScopes(): array
    {
        return $this->getScopeListSetting('userClientScopes');
    }

    /**
     * @return string[] the upper bound of scopes for personal access tokens
     */
    public function getPatScopes(): array
    {
        return $this->getScopeListSetting('patScopes');
    }

    public function getRefreshTokenLifetimeDays(): int
    {
        return max(1, (int)$this->settings->get('refreshTokenLifetimeDays', 90));
    }

    public function getGrantMaxLifetimeDays(): int
    {
        return max(0, (int)$this->settings->get('grantMaxLifetimeDays', 0));
    }

    /**
     * @return int maximum PAT lifetime in days, 0 = unlimited (offers "never")
     */
    public function getPatMaxLifetimeDays(): int
    {
        return max(0, (int)$this->settings->get('patMaxLifetimeDays', 365));
    }

    public function revokesOnPasswordChange(): bool
    {
        return (bool)$this->settings->get('revokeOnPasswordChange', false);
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer !== null) {
            return [];
        }

        return [
            new permissions\CreatePersonalAccessToken(),
            new permissions\ManageOwnOAuthClients(),
        ];
    }

    private function getScopeListSetting(string $name): array
    {
        $value = $this->settings->get($name);
        if ($value === null) {
            return ScopeRegistry::getDefaultRestrictedScopeIds();
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }
}
