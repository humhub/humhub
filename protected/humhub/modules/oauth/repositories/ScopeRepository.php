<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\repositories;

use humhub\modules\oauth\entities\ClientEntity;
use humhub\modules\oauth\entities\ScopeEntity;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * @since 1.20
 */
class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * Any well-formed id, registered or not: scopes are enforced from the grant, and a scope
     * whose module was disabled must not fail a refresh after its token was already claimed.
     */
    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        return strlen($identifier) <= 80 && preg_match('/^[a-z0-9][a-z0-9:_-]*$/', $identifier)
            ? new ScopeEntity($identifier)
            : null;
    }

    /**
     * Applied at code exchange and refresh: the grant must still be usable, and the token
     * gets the scopes the grant holds right now. An empty set is not an error, the token then
     * simply grants nothing — throwing here would burn the already claimed refresh token.
     */
    public function finalizeScopes(
        array $scopes,
        string $grantType,
        ClientEntityInterface $clientEntity,
        ?string $userIdentifier = null,
        ?string $authCodeId = null,
    ): array {
        $grant = Grant::findByGuid($userIdentifier);
        $service = new GrantService();

        if (
            $grant === null
            || $grant->type !== Grant::TYPE_OAUTH
            || !$clientEntity instanceof ClientEntity
            || (int)$grant->client_id !== $clientEntity->recordId
            || !$service->isUsable($grant)
        ) {
            throw OAuthServerException::invalidGrant('The authorization is no longer valid.');
        }

        return array_map(static fn(string $id) => new ScopeEntity($id), $service->getEffectiveScopes($grant));
    }
}
