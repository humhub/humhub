<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\permissions\ManageOwnOAuthClients;
use humhub\modules\user\widgets\security\SecuritySection;
use Yii;

/**
 * The OAuth clients the user registered for their own account.
 *
 * @since 1.20
 */
class OwnClientsSection extends SecuritySection
{
    public function getTitle(): string
    {
        return Yii::t('OauthModule.base', 'My OAuth apps');
    }

    public function getDescription(): string
    {
        return Yii::t('OauthModule.base', 'Apps you registered yourself. Only you can connect them to your account.');
    }

    public function isVisible(): bool
    {
        return self::canManage();
    }

    public static function canManage(): bool
    {
        return !Yii::$app->user->isGuest
            && Module::instance()->allowsUserClients()
            && Yii::$app->user->can(ManageOwnOAuthClients::class);
    }

    protected function renderContent(): string
    {
        return $this->render('own-clients', [
            'clients' => Client::find()
                ->where(['owner_user_id' => Yii::$app->user->id, 'type' => Client::TYPE_USER])
                ->orderBy(['name' => SORT_ASC])
                ->all(),
        ]);
    }
}
