<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models;

use humhub\components\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * An issued authorization code, single use.
 *
 * @property string $id
 * @property int $grant_id
 * @property int $expires_at
 * @property int $is_revoked
 *
 * @property-read Grant $grant
 *
 * @since 1.20
 */
class AuthCode extends ActiveRecord
{
    public static function tableName()
    {
        return 'oauth_auth_code';
    }

    public function getGrant(): ActiveQuery
    {
        return $this->hasOne(Grant::class, ['id' => 'grant_id']);
    }

    public function isValid(): bool
    {
        return !$this->is_revoked && $this->expires_at >= time();
    }
}
