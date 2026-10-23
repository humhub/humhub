<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\repositories;

use humhub\modules\oauth\entities\ClientEntity;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\services\GrantService;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

/**
 * @since 1.20
 */
class ClientRepository implements ClientRepositoryInterface
{
    public const GRANT_TYPES = ['authorization_code', 'refresh_token'];

    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        $client = $this->findUsableClient($clientIdentifier);
        return $client === null ? null : new ClientEntity($client);
    }

    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
    {
        if ($grantType !== null && !in_array($grantType, self::GRANT_TYPES, true)) {
            return false;
        }

        return $this->authenticateClient($clientIdentifier, $clientSecret) !== null;
    }

    /**
     * @return Client|null the usable client the credentials belong to
     */
    public function authenticateClient(string $clientIdentifier, ?string $clientSecret): ?Client
    {
        $client = $this->findUsableClient($clientIdentifier);
        if ($client === null) {
            return null;
        }

        $valid = $client->isConfidential() ? $client->validateSecret($clientSecret) : $clientSecret === null;
        return $valid ? $client : null;
    }

    public function findUsableClient(string $clientIdentifier): ?Client
    {
        $client = Client::findByClientId($clientIdentifier);
        if ($client === null || !$client->is_active || !(new GrantService())->isClientTypeAllowed($client)) {
            return null;
        }

        return $client;
    }
}
