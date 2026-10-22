<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\repositories;

use humhub\modules\oauth\components\ScopeRegistry;
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
    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        return ScopeRegistry::has($identifier) ? new ScopeEntity($identifier) : null;
    }

    /**
     * Applied at code exchange and refresh: the grant must still be usable, and the token
     * gets the requested scopes the grant holds right now.
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

        $effective = $service->getEffectiveScopes($grant);

        $granted = array_values(array_filter(
            $scopes,
            static fn(ScopeEntityInterface $scope) => in_array($scope->getIdentifier(), $effective, true),
        ));

        if ($granted === []) {
            throw OAuthServerException::invalidScope('');
        }

        return $granted;
    }
}
