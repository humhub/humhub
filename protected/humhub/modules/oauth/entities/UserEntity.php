<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\entities;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * The league user: identified by the grant's guid, so every code and token resolves to its grant.
 *
 * @since 1.20
 */
class UserEntity implements UserEntityInterface
{
    use EntityTrait;

    public function __construct(string $grantGuid)
    {
        $this->setIdentifier($grantGuid);
    }
}
