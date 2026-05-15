<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

/**
 * @since 1.1
 * @deprecated since 1.19 — configure on the UserSource via `$approval` / `$trustedAuthClientIds`
 *   and have the UserSource list the client in `$allowedAuthClientIds` for trusted
 *   self-registration. The interface is kept as an empty marker so modules still
 *   implementing it don't fatal-error; core no longer reads it.
 */
interface ApprovalBypass
{
}
