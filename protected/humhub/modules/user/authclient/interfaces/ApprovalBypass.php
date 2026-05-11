<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

/**
 * Auth clients implementing this interface bypass the UserSource approval requirement
 * and may auto-register users regardless of the `auth.anonymousRegistration` setting.
 *
 * @since 1.1
 * @deprecated since 1.19 — configure on the UserSource via `$approval` / `$trustedAuthClientIds`
 *   and rely on the UserSource declaring the client in `$allowedAuthClientIds` for trusted
 *   self-registration. The interface still works as a fallback but will be removed in a
 *   future release.
 */
interface ApprovalBypass
{
}
