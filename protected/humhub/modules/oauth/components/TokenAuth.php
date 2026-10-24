<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use humhub\components\psr\ServerRequestFactory;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use League\OAuth2\Server\Exception\OAuthServerException;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\HttpException;
use yii\web\IdentityInterface;
use yii\web\User;

/**
 * Authenticates API requests with a bearer token of the OAuth server: an access token (JWT)
 * or a personal access token (`hhpat_` prefix).
 *
 * A token that is not one of ours (no `hhpat_` prefix, no `hh-` key id) yields `null`, so the
 * next method may try (e.g. the rest module's JWT). One of ours that fails validation is a 401.
 *
 * The identity is set without a login: no `last_login` write and no login events per request.
 *
 * @since 1.20
 */
class TokenAuth extends AuthMethod
{
    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $module = Module::instance();
        $module->activeGrant = null;

        $header = $request->getHeaders()->get('Authorization');
        if (!is_string($header) || !preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return null;
        }

        $token = $matches[1];
        if (str_starts_with($token, PersonalAccessTokenService::PREFIX)) {
            $grant = (new PersonalAccessTokenService())->find($token);
            if ($grant === null || !(new GrantService())->isUsable($grant)) {
                $this->fail($response);
            }

            return $this->authenticateGrant($user, $grant);
        }

        if (!TokenResolver::isOwnJwt($token)) {
            return null;
        }

        try {
            $validated = AuthorizationServerFactory::createResourceServer()
                ->validateAuthenticatedRequest(ServerRequestFactory::fromYii($request));
        } catch (OAuthServerException) {
            $this->fail($response);
        }

        $grant = Grant::findByGuid($validated->getAttribute('oauth_user_id'));
        if ($grant === null || $grant->user === null) {
            $this->fail($response);
        }

        return $this->authenticateGrant($user, $grant);
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', 'Bearer realm="api"');
    }

    /**
     * Sets the grant's user as the identity of the session-less user component.
     */
    protected function authenticateGrant(User $user, Grant $grant): IdentityInterface
    {
        $identity = $grant->user;
        $user->setIdentity($identity);

        Module::instance()->activeGrant = $grant;
        (new GrantService())->touch($grant);

        return $identity;
    }

    /**
     * Not an `UnauthorizedHttpException`: `AuthMethod` turns that into a guest request on
     * optional (guest-allowed) actions, and an invalid token must never become one.
     *
     * @throws HttpException
     */
    private function fail($response): never
    {
        $this->challenge($response);
        throw new HttpException(401, Yii::t('yii', 'Your request was made with invalid credentials.'));
    }
}
