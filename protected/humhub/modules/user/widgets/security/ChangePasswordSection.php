<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\models\Password;
use humhub\modules\user\services\UserSourceService;
use Yii;

/**
 * @since 1.20
 */
class ChangePasswordSection extends SecuritySection
{
    public ?Password $model = null;

    public function getTitle(): string
    {
        return Yii::t('UserModule.account', 'Change password');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'Your current password can be changed here.');
    }

    public function isVisible(): bool
    {
        return UserSourceService::getForUser()->canChangePassword();
    }

    protected function renderContent(): string
    {
        $model = $this->model ?? new Password(['scenario' => 'changePassword']);

        return $this->render('change-password', ['model' => $model]);
    }
}
