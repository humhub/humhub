<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\account;

use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\widgets\SettingsSection;
use Yii;

/**
 * Settings without a section of their own: the introduction tour
 *
 * @since 1.20
 */
class OtherSettingsSection extends SettingsSection
{
    public ?AccountSettings $model = null;

    public function getTitle(): string
    {
        return Yii::t('UserModule.account', 'Other');
    }

    public function isVisible(): bool
    {
        return Yii::$app->getModule('tour')->settings->get('enable') == 1;
    }

    public function hasErrors(): bool
    {
        return $this->model?->hasErrors() ?? false;
    }

    protected function renderContent(): string
    {
        $model = $this->model ?? new AccountSettings(['user' => Yii::$app->user->getIdentity()]);

        return $this->render('other-settings', ['model' => $model]);
    }
}
