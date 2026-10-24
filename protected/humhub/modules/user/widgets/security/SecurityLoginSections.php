<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\models\Password;

/**
 * Sections of the "Login" tab: how the user signs in
 *
 * @since 1.20
 */
class SecurityLoginSections extends SecuritySectionStack
{
    /**
     * @var Password|null the password form model, to show validation errors after a submit
     */
    public ?Password $passwordModel = null;

    public function init()
    {
        $this->addWidget(ChangePasswordSection::class, ['model' => $this->passwordModel], ['sortOrder' => 100]);
        $this->addWidget(ConnectedAccountsSection::class, [], ['sortOrder' => 200]);

        parent::init();
    }
}
