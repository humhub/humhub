<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\widgets\SettingsSectionStack;

/**
 * Sections of the "My Data" tab, e.g. a data export of a module and the account deletion
 *
 * @since 1.20
 */
class SecurityDataSections extends SettingsSectionStack
{
    public function init()
    {
        $this->addWidget(DeleteAccountSection::class, [], ['sortOrder' => 900]);

        parent::init();
    }
}
