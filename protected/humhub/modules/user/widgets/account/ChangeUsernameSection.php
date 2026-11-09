<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\account;

use humhub\modules\user\models\forms\AccountChangeUsername;
use humhub\modules\user\services\UserSourceService;
use humhub\modules\user\widgets\SettingsSection;
use Yii;

/**
 * @since 1.20
 */
class ChangeUsernameSection extends SettingsSection
{
    public ?AccountChangeUsername $model = null;

    public function getTitle(): string
    {
        return Yii::t('UserModule.base', 'Username');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'Your current username is {username}.', ['username' => Yii::$app->user->getIdentity()->username]);
    }

    public function isVisible(): bool
    {
        return UserSourceService::getForUser()->canChangeUsername();
    }

    public function hasErrors(): bool
    {
        return $this->model?->hasErrors() ?? false;
    }

    protected function renderContent(): string
    {
        return $this->render('change-username', ['model' => $this->model ?? new AccountChangeUsername()]);
    }
}
