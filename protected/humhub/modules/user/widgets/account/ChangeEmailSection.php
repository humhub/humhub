<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\account;

use humhub\modules\user\models\forms\AccountChangeEmail;
use humhub\modules\user\services\UserSourceService;
use humhub\modules\user\widgets\SettingsSection;
use Yii;

/**
 * @since 1.20
 */
class ChangeEmailSection extends SettingsSection
{
    public ?AccountChangeEmail $model = null;

    public function getTitle(): string
    {
        return Yii::t('UserModule.account', 'E-Mail address');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'Your current e-mail address is {email}.', ['email' => Yii::$app->user->getIdentity()->email]);
    }

    public function isVisible(): bool
    {
        return UserSourceService::getForUser()->canChangeEmail();
    }

    public function hasErrors(): bool
    {
        return $this->model?->hasErrors() ?? false;
    }

    protected function renderContent(): string
    {
        return $this->render('change-email', ['model' => $this->model ?? new AccountChangeEmail()]);
    }
}
