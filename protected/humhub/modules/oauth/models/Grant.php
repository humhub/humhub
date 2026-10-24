<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models;

use humhub\components\ActiveRecord;
use humhub\libs\UUID;
use humhub\modules\user\models\User;
use yii\db\ActiveQuery;

/**
 * An authorization a user holds: a connected OAuth client or a personal access token.
 *
 * Deleting the row revokes it — the foreign keys remove its codes and tokens.
 *
 * @property int $id
 * @property string $guid
 * @property int $user_id
 * @property string $type
 * @property int|null $client_id
 * @property string $name
 * @property string $scopes JSON list
 * @property string|null $token_hash
 * @property string|null $token_hint
 * @property string|null $user_agent
 * @property int $created_at
 * @property int|null $last_used_at
 * @property int|null $expires_at
 *
 * @property-read User $user
 * @property-read Client|null $client
 *
 * @since 1.20
 */
class Grant extends ActiveRecord
{
    public const TYPE_OAUTH = 'oauth';
    public const TYPE_PAT = 'pat';

    public static function tableName()
    {
        return 'oauth_grant';
    }

    public static function findByGuid(?string $guid): ?self
    {
        return $guid === null || $guid === '' ? null : static::findOne(['guid' => $guid]);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->guid ??= UUID::v4();
            $this->created_at ??= time();
        }

        return parent::beforeSave($insert);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * @return string[]
     */
    public function getScopeIds(): array
    {
        return Client::decodeList($this->scopes);
    }

    /**
     * @param string[] $scopeIds
     */
    public function setScopeIds(array $scopeIds): void
    {
        $this->scopes = json_encode(array_values(array_unique($scopeIds)));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at < time();
    }
}
