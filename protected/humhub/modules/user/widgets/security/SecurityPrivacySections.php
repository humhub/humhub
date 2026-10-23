<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\models\forms\PrivacySettings;

/**
 * Sections of the "Privacy" tab: who can see the profile and what others may do on it
 *
 * @since 1.20
 */
class SecurityPrivacySections extends SecuritySectionStack
{
    /**
     * @var PrivacySettings|null the privacy settings form model, to show validation errors after a submit
     */
    public ?PrivacySettings $privacyModel = null;

    public function init()
    {
        $this->addWidget(PrivacySettingsSection::class, ['model' => $this->privacyModel], ['sortOrder' => 100]);
        $this->addWidget(ProfilePermissionsSection::class, [], ['sortOrder' => 200]);

        parent::init();
    }
}
