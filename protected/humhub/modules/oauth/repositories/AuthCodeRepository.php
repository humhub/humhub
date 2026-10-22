<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\repositories;

use humhub\modules\oauth\entities\AuthCodeEntity;
use humhub\modules\oauth\models\AuthCode;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

/**
 * @since 1.20
 */
class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCodeEntity();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $grant = Grant::findByGuid($authCodeEntity->getUserIdentifier());
        if ($grant === null) {
            throw OAuthServerException::invalidGrant('The authorization is no longer valid.');
        }

        (new AuthCode([
            'id' => $authCodeEntity->getIdentifier(),
            'grant_id' => $grant->id,
            'expires_at' => $authCodeEntity->getExpiryDateTime()->getTimestamp(),
            'is_revoked' => 0,
        ]))->save(false);
    }

    public function revokeAuthCode(string $codeId): void
    {
        AuthCode::updateAll(['is_revoked' => 1], ['id' => $codeId]);
    }

    /**
     * A code used twice revokes everything issued from it (RFC 6749 §4.1.2).
     */
    public function isAuthCodeRevoked(string $codeId): bool
    {
        $code = AuthCode::findOne(['id' => $codeId]);
        if ($code === null) {
            return true;
        }

        $service = new GrantService();

        if ($code->is_revoked) {
            $service->revoke($code->grant);
            return true;
        }

        return !$service->isUsable($code->grant);
    }
}
