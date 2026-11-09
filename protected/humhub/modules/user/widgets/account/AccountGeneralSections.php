<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\account;

use humhub\modules\user\models\forms\AccountChangeEmail;
use humhub\modules\user\models\forms\AccountChangeUsername;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\widgets\SettingsSectionStack;

/**
 * The sections of the "General" tab of the account settings, as an accordion: e-mail address (open), username and
 * "Other" (introduction tour)
 *
 * Modules add sections on `EVENT_INIT`; sort orders of the core sections: 100, 200, 300.
 *
 * @since 1.20
 */
class AccountGeneralSections extends SettingsSectionStack
{
    /**
     * @inheritdoc
     */
    public bool $accordion = true;

    /**
     * @var AccountChangeUsername|null the submitted form model, to show its validation errors
     */
    public ?AccountChangeUsername $usernameModel = null;

    /**
     * @var AccountChangeEmail|null the submitted form model, to show its validation errors
     */
    public ?AccountChangeEmail $emailModel = null;

    /**
     * @var AccountSettings|null the submitted form model, to show its validation errors
     */
    public ?AccountSettings $settingsModel = null;

    public function init()
    {
        $this->addWidget(ChangeEmailSection::class, ['model' => $this->emailModel], ['sortOrder' => 100]);
        $this->addWidget(ChangeUsernameSection::class, ['model' => $this->usernameModel], ['sortOrder' => 200]);
        $this->addWidget(OtherSettingsSection::class, ['model' => $this->settingsModel], ['sortOrder' => 300]);

        parent::init();
    }
}
