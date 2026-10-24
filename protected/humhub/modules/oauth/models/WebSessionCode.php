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
 * A single-use code that turns an app grant into a web session.
 *
 * @property string $code_hash
 * @property int $grant_id
 * @property int $expires_at
 *
 * @property-read Grant $grant
 *
 * @since 1.20
 */
class WebSessionCode extends ActiveRecord
{
    public static function tableName()
    {
        return 'oauth_web_session_code';
    }

    public function getGrant(): ActiveQuery
    {
        return $this->hasOne(Grant::class, ['id' => 'grant_id']);
    }
}
