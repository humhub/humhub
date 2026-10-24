<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\services\UserSourceService;
use humhub\widgets\bootstrap\Button;
use Yii;

/**
 * @since 1.20
 */
class DeleteAccountSection extends SecuritySection
{
    public function getTitle(): string
    {
        return Yii::t('UserModule.account', 'Delete account');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'All your personal data will be irrevocably deleted.');
    }

    public function isVisible(): bool
    {
        return UserSourceService::getForUser()->canDeleteAccount();
    }

    protected function renderContent(): string
    {
        return (string)Button::danger(Yii::t('UserModule.account', 'Delete account'))
            ->link(['/user/security/delete'])
            ->sm();
    }
}
