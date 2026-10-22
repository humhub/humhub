<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\repositories;

use humhub\modules\oauth\entities\RefreshTokenEntity;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\RefreshToken;
use humhub\modules\oauth\services\GrantService;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Yii;

/**
 * @since 1.20
 */
class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $accessToken = $refreshTokenEntity->getAccessToken();
        $grant = Grant::findByGuid($accessToken->getUserIdentifier());
        if ($grant === null) {
            throw OAuthServerException::invalidGrant('The authorization is no longer valid.');
        }

        (new RefreshToken([
            'id' => $refreshTokenEntity->getIdentifier(),
            'grant_id' => $grant->id,
            'access_token_id' => $accessToken->getIdentifier(),
            'expires_at' => $refreshTokenEntity->getExpiryDateTime()->getTimestamp(),
            'is_revoked' => 0,
        ]))->save(false);
    }

    public function revokeRefreshToken(string $tokenId): void
    {
        RefreshToken::updateAll(['is_revoked' => 1], ['id' => $tokenId]);
    }

    /**
     * A revoked refresh token presented again was copied: its grant is revoked as a whole.
     */
    public function isRefreshTokenRevoked(string $tokenId): bool
    {
        $token = RefreshToken::findOne(['id' => $tokenId]);
        if ($token === null) {
            return true;
        }

        $service = new GrantService();

        if ($token->is_revoked) {
            Yii::warning('A revoked refresh token was reused; revoking grant ' . $token->grant_id . '.', 'oauth');
            $service->revoke($token->grant);
            return true;
        }

        return $token->expires_at < time() || !$service->isUsable($token->grant);
    }
}
