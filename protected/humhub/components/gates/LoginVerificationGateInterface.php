<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

/**
 * Marks a gate that verifies the login itself, e.g. a second factor.
 *
 * Such a gate does not intercept a session established from an issued credential (see
 * [[GateManager::SESSION_KEY_ISSUED_CREDENTIAL]]): it already ran when that credential was
 * issued. Gates about the account's state (terms, password change) must not implement it.
 *
 * @since 1.20
 */
interface LoginVerificationGateInterface
{
}
