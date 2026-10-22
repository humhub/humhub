<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\modules\oauth\models\Grant;
use humhub\modules\user\widgets\security\SecuritySectionStack;
use Yii;

/**
 * The sections of the "Apps & Tokens" tab.
 *
 * @since 1.20
 */
class AppsSections extends SecuritySectionStack
{
    /**
     * Whether the current user gets the tab: with a grant, or when allowed to create tokens or clients.
     */
    public static function isAvailable(): bool
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return false;
        }

        return Grant::find()->where(['user_id' => $user->id])->exists()
            || TokensSection::canCreate()
            || OwnClientsSection::canManage();
    }

    public function init()
    {
        $this->addWidget(DevicesSection::class, [], ['sortOrder' => 100]);
        $this->addWidget(ConnectedAppsSection::class, [], ['sortOrder' => 200]);
        $this->addWidget(TokensSection::class, [], ['sortOrder' => 300]);
        $this->addWidget(OwnClientsSection::class, [], ['sortOrder' => 400]);

        parent::init();
    }
}
