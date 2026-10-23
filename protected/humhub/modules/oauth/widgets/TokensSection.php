<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\permissions\CreatePersonalAccessToken;
use Yii;
use yii\db\ActiveQuery;

/**
 * The user's personal access tokens.
 *
 * @since 1.20
 */
class TokensSection extends GrantSection
{
    public function getTitle(): string
    {
        return Yii::t('OauthModule.base', 'Personal access tokens');
    }

    public function getDescription(): string
    {
        return Yii::t('OauthModule.base', 'Tokens for scripts and agents that access your account via the API.');
    }

    public function isVisible(): bool
    {
        return $this->getGrants() !== [] || self::canCreate();
    }

    public static function canCreate(): bool
    {
        return Module::instance()->allowsPersonalAccessTokens()
            && Yii::$app->user->can(CreatePersonalAccessToken::class);
    }

    protected function createQuery(): ActiveQuery
    {
        return Grant::find()
            ->where(['user_id' => self::getUserId(), 'type' => Grant::TYPE_PAT])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    protected function renderContent(): string
    {
        $scopeLabels = [];
        foreach ($this->getGrants() as $grant) {
            $scopeLabels[$grant->id] = self::getScopeLabels($grant);
        }

        return $this->render('tokens', [
            'grants' => $this->getGrants(),
            'scopeLabels' => $scopeLabels,
            'canCreate' => self::canCreate(),
        ]);
    }
}
