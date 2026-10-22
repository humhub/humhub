<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use Yii;
use yii\db\ActiveQuery;

/**
 * The devices signed in with the HumHub app.
 *
 * @since 1.20
 */
class DevicesSection extends GrantSection
{
    public function getTitle(): string
    {
        return Yii::t('OauthModule.base', 'Devices');
    }

    public function getDescription(): string
    {
        return Yii::t('OauthModule.base', 'Devices signed in with the HumHub app.');
    }

    public function isVisible(): bool
    {
        return $this->getGrants() !== [];
    }

    protected function createQuery(): ActiveQuery
    {
        // A grant whose code was never exchanged has no device signed in
        return Grant::find()
            ->innerJoinWith('client client')
            ->where([
                'oauth_grant.user_id' => self::getUserId(),
                'oauth_grant.type' => Grant::TYPE_OAUTH,
                'client.type' => Client::TYPE_SYSTEM,
            ])
            ->andWhere(['not', ['oauth_grant.last_used_at' => null]])
            ->orderBy(['oauth_grant.last_used_at' => SORT_DESC]);
    }

    protected function renderContent(): string
    {
        return $this->render('devices', ['grants' => $this->getGrants()]);
    }
}
