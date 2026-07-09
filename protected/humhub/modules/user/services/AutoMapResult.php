<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

/**
 * Outcome of {@see AuthClientService::autoMapToExistingUser()}.
 *
 * @since 1.19
 */
enum AutoMapResult
{
    case Mapped;
    case NotFound;
    case Denied;
}
