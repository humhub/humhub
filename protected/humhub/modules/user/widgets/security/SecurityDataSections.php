<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

/**
 * Sections of the "My Data" tab, e.g. a data export of a module and the account deletion
 *
 * @since 1.20
 */
class SecurityDataSections extends SecuritySectionStack
{
    public function init()
    {
        $this->addWidget(DeleteAccountSection::class, [], ['sortOrder' => 900]);

        parent::init();
    }
}
