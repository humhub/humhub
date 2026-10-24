<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\repositories;

use humhub\modules\oauth\entities\AccessTokenEntity;
use humhub\modules\oauth\models\AccessToken;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Yii;
use yii\db\IntegrityException;
use yii\web\Request;

/**
 * @since 1.20
 */
class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, ?string $userIdentifier = null): AccessTokenEntityInterface
    {
        $module = Module::instance();

        $token = new AccessTokenEntity();
        $token->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $token->addScope($scope);
        }
        if ($userIdentifier !== null) {
            $token->setUserIdentifier($userIdentifier);
        }
        $token->issuer = $module->getIssuer();
        $token->keyId = $module->getKeyStore()->getKeyId();

        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $grant = Grant::findByGuid($accessTokenEntity->getUserIdentifier());
        if ($grant === null) {
            throw OAuthServerException::invalidGrant('The authorization is no longer valid.');
        }

        $row = new AccessToken([
            'id' => $accessTokenEntity->getIdentifier(),
            'grant_id' => $grant->id,
            'expires_at' => $accessTokenEntity->getExpiryDateTime()->getTimestamp(),
            'is_revoked' => 0,
        ]);

        try {
            $row->save(false);
        } catch (IntegrityException) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        // the first token of a grant records the device it was issued to
        if ($grant->last_used_at === null && $grant->user_agent === null && Yii::$app->request instanceof Request) {
            $userAgent = Yii::$app->request->getUserAgent();
            if (!empty($userAgent)) {
                $grant->updateAttributes(['user_agent' => mb_substr($userAgent, 0, 255)]);
            }
        }
        (new GrantService())->touch($grant);
    }

    public function revokeAccessToken(string $tokenId): void
    {
        AccessToken::updateAll(['is_revoked' => 1], ['id' => $tokenId]);
    }

    public function isAccessTokenRevoked(string $tokenId): bool
    {
        $token = AccessToken::findOne(['id' => $tokenId]);
        if ($token === null || !$token->isValid()) {
            return true;
        }

        return !(new GrantService())->isUsable($token->grant);
    }
}
