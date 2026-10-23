<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

/**
 * Sections of the "Devices & Access" tab: where the account is signed in and what else has access to it
 *
 * @since 1.20
 */
class SecuritySessionSections extends SecuritySectionStack
{
    public function init()
    {
        $this->addWidget(BrowserSessionsSection::class, [], ['sortOrder' => 100]);

        parent::init();
    }
}
