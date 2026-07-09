<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use Yii;
use yii\authclient\ClientInterface;

/**
 * Bridges an in-flight authentication across the request boundary between
 * "auth client succeeded" (AuthController) and "registration form
 * submitted" (RegistrationController).
 *
 * Stores only data (client id + normalised user attributes), never the
 * live AuthClient instance. On the receiving side, the client is
 * reconstructed from the AuthClient collection and re-hydrated with the
 * captured attributes — closures in normalize maps, connection handles,
 * and other non-serialisable state never enter the session.
 *
 * Replaces the pre-1.19 pattern of stashing the AuthClient in the session
 * directly + the `SerializableAuthClient::beforeSerialize()` workaround.
 *
 * @since 1.19
 */
final class PendingAuthService
{
    private const SESSION_KEY = 'pendingAuth';

    /**
     * Captures the AuthClient's identity + already-normalised attributes
     * into session storage. Safe to call with any ClientInterface.
     */
    public function store(ClientInterface $authClient): void
    {
        Yii::$app->session->set(self::SESSION_KEY, [
            'id'         => $authClient->getId(),
            'attributes' => $authClient->getUserAttributes(),
        ]);
    }

    /**
     * Whether a captured auth state is waiting to be consumed.
     */
    public function hasPending(): bool
    {
        $data = Yii::$app->session->get(self::SESSION_KEY);
        return is_array($data) && !empty($data['id']);
    }

    /**
     * Returns the in-flight AuthClient — reconstructed from the auth-client
     * collection's config and re-hydrated with the captured user
     * attributes. Returns null when nothing is pending or the referenced
     * client is no longer registered.
     */
    public function restore(): ?ClientInterface
    {
        $data = Yii::$app->session->get(self::SESSION_KEY);
        if (!is_array($data) || empty($data['id'])) {
            return null;
        }
        if (!Yii::$app->authClientCollection->hasClient($data['id'])) {
            return null;
        }
        $client = Yii::$app->authClientCollection->getClient($data['id']);
        $client->setUserAttributes($data['attributes'] ?? []);
        return $client;
    }

    /**
     * Clears the pending-auth state. Call after consumption (successful
     * registration, abandoned flow, …) so a stale entry doesn't get
     * re-applied on a subsequent registration attempt.
     */
    public function clear(): void
    {
        Yii::$app->session->remove(self::SESSION_KEY);
    }
}
