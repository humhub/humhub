<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models;

use humhub\components\ActiveRecord;
use Yii;

/**
 * An OAuth client.
 *
 * @property int $id
 * @property string $client_id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property string|null $secret_hash
 * @property string $redirect_uris JSON list
 * @property string $scopes JSON list of allowed scopes
 * @property int $skip_consent
 * @property int $is_active
 * @property int $created_at
 * @property int|null $created_by
 *
 * @since 1.20
 */
class Client extends ActiveRecord
{
    public const TYPE_SYSTEM = 'system';
    public const TYPE_ADMIN = 'admin';

    /**
     * The built-in client of the HumHub app.
     */
    public const SYSTEM_CLIENT_ID = 'humhub-app';

    public static function tableName()
    {
        return 'oauth_client';
    }

    public static function findByClientId(string $clientId): ?self
    {
        return static::findOne(['client_id' => $clientId]);
    }

    public function isConfidential(): bool
    {
        return $this->secret_hash !== null;
    }

    public function validateSecret(?string $secret): bool
    {
        return $secret !== null && $this->secret_hash !== null
            && hash_equals($this->secret_hash, hash('sha256', $secret));
    }

    /**
     * Sets a new secret and returns it in plain text: only its hash is stored. The caller saves the client.
     */
    public function generateSecret(): string
    {
        $secret = Yii::$app->security->generateRandomString(48);
        $this->secret_hash = hash('sha256', $secret);

        return $secret;
    }

    /**
     * @return string[]
     */
    public function getRedirectUris(): array
    {
        return self::decodeList($this->redirect_uris);
    }

    /**
     * @return string[]
     */
    public function getAllowedScopes(): array
    {
        return self::decodeList($this->scopes);
    }

    public static function decodeList(?string $json): array
    {
        $decoded = json_decode((string)$json, true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }
}
