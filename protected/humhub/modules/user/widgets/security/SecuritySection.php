<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\widgets\SettingsSection;

/**
 * A section in a tab of the security settings
 *
 * Modules add sections to [[SecurityLoginSections]], [[SecuritySessionSections]], [[SecurityPrivacySections]] or
 * [[SecurityDataSections]] on their `EVENT_INIT`.
 *
 * @since 1.20
 */
abstract class SecuritySection extends SettingsSection
{
}
