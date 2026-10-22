<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use humhub\modules\oauth\models\Client;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Shared by the controllers handing out credentials (client secrets, tokens).
 *
 * @since 1.20
 */
trait CredentialActions
{
    /**
     * An impersonating administrator must not create credentials that outlive the impersonation.
     */
    protected function denyWhileImpersonating(): void
    {
        if (Yii::$app->user->getImpersonation()->isActive()) {
            throw new ForbiddenHttpException(Yii::t('OauthModule.base', 'Credentials cannot be created while impersonating a user.'));
        }
    }

    protected function regenerateSecret(Client $client): string
    {
        if (!$client->isConfidential()) {
            throw new BadRequestHttpException();
        }

        $secret = $client->generateSecret();
        $client->save(false);

        return $this->renderSecret($client, $secret);
    }

    /**
     * Rendered, not redirected to: the secret must not pass through the session or any cache.
     */
    protected function renderSecret(Client $client, string $secret): string
    {
        Yii::$app->response->headers->set('Cache-Control', 'no-store');

        return $this->render('client-secret', ['client' => $client, 'secret' => $secret]);
    }
}
