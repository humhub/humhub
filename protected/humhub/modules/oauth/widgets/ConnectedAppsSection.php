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
 * The apps the user connected to the account: every client but the HumHub app.
 *
 * @since 1.20
 */
class ConnectedAppsSection extends GrantSection
{
    public function getTitle(): string
    {
        return Yii::t('OauthModule.base', 'Connected apps');
    }

    public function getDescription(): string
    {
        return Yii::t('OauthModule.base', 'Apps you allowed to access your account.');
    }

    public function isVisible(): bool
    {
        return $this->getGrants() !== [];
    }

    protected function createQuery(): ActiveQuery
    {
        // A grant whose code was never exchanged has not connected anything
        return Grant::find()
            ->innerJoinWith('client client')
            ->where([
                'oauth_grant.user_id' => self::getUserId(),
                'oauth_grant.type' => Grant::TYPE_OAUTH,
            ])
            ->andWhere(['!=', 'client.type', Client::TYPE_SYSTEM])
            ->andWhere(['not', ['oauth_grant.last_used_at' => null]])
            ->orderBy(['oauth_grant.created_at' => SORT_DESC]);
    }

    protected function renderContent(): string
    {
        $scopeLabels = [];
        foreach ($this->getGrants() as $grant) {
            $scopeLabels[$grant->id] = self::getScopeLabels($grant);
        }

        return $this->render('connected-apps', ['grants' => $this->getGrants(), 'scopeLabels' => $scopeLabels]);
    }
}
