<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\modules\user\helpers\UserAgentHelper;

/**
 * An active session of a user as shown in the security settings
 *
 * @since 1.20
 */
final class UserSession
{
    public readonly string $device;
    public readonly bool $isMobile;

    /**
     * @param string $key hash of the session id - the session id itself never leaves the server
     */
    public function __construct(
        public readonly string $key,
        public readonly bool $isCurrent,
        public readonly ?int $loggedInAt,
        public readonly int $lastActivity,
        public readonly ?string $userAgent,
    ) {
        $this->device = UserAgentHelper::describe($userAgent);
        $this->isMobile = UserAgentHelper::isMobile($userAgent);
    }
}
