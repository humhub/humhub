<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\entities;

use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\repositories\ClientRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/**
 * Carries only scalars, because authorization requests holding it are stored in the session.
 *
 * @since 1.20
 */
class ClientEntity implements ClientEntityInterface
{
    use ClientTrait;
    use EntityTrait;

    public readonly int $recordId;
    public readonly string $type;

    public function __construct(Client $client)
    {
        $this->setIdentifier($client->client_id);
        $this->name = $client->name;
        $this->redirectUri = $client->getRedirectUris();
        $this->isConfidential = $client->isConfidential();
        $this->recordId = $client->id;
        $this->type = $client->type;
    }

    /**
     * League skips the repository's grant check for public clients, so it is enforced here too.
     */
    public function supportsGrantType(string $grantType): bool
    {
        return in_array($grantType, ClientRepository::GRANT_TYPES, true);
    }

    public function getRecord(): ?Client
    {
        return Client::findOne(['id' => $this->recordId]);
    }
}
