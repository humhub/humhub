<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\models\forms\PrivacySettings;
use Yii;

/**
 * @since 1.20
 */
class PrivacySettingsSection extends SecuritySection
{
    public ?PrivacySettings $model = null;

    public function getTitle(): string
    {
        return Yii::t('UserModule.base', 'Visibility');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'Decide who can see your profile and your online status, block users and manage trusted sources of external content.');
    }

    public function isVisible(): bool
    {
        return $this->getModel()->hasFields();
    }

    protected function renderContent(): string
    {
        return $this->render('privacy-settings', ['model' => $this->getModel()]);
    }

    private function getModel(): PrivacySettings
    {
        return $this->model ??= new PrivacySettings();
    }
}
