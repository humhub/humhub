<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\tests\codeception\_support;

use humhub\modules\dav\components\HttpBridge;
use humhub\modules\dav\components\ServerFactory;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module as OAuthModule;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use Sabre\HTTP\Request;
use Yii;
use yii\web\Response;

/**
 * Runs WebDAV requests in-process, as the endpoint does once a request has authenticated:
 * the server of the grant, the request handed to Sabre, Sabre's response into Yii's.
 *
 * Also usable by modules for their mounts:
 *
 * ```php
 * $client = DavTestClient::forUser(User::findOne(['id' => 2]), ['cfiles:read', 'cfiles:write']);
 * $response = $client->request('PROPFIND', 'files/', ['Depth' => '1']);
 * ```
 */
class DavTestClient
{
    public const BASE_URI = '/dav/';

    public function __construct(public readonly Grant $grant)
    {
    }

    /**
     * A personal access token of the user with these scopes. Tokens are allowed and their
     * upper bound holds these scopes, which must be registered.
     *
     * @param string[] $scopes
     */
    public static function forUser(User $user, array $scopes): self
    {
        $module = OAuthModule::instance();
        $module->settings->set('allowPersonalAccessTokens', true);
        $module->settings->set('patScopes', json_encode(array_values(array_unique(array_merge($module->getPatScopes(), $scopes)))));
        ScopeRegistry::reset();

        [, $grant] = (new PersonalAccessTokenService())->create($user, 'DAV test', $scopes, 30);

        return new self($grant);
    }

    /**
     * @param string $path below `/dav/`
     * @param array<string, string> $headers
     * @param resource|string|null $body
     */
    public function request(string $method, string $path, array $headers = [], mixed $body = null): Response
    {
        Yii::$app->user->setIdentity($this->grant->user);
        OAuthModule::instance()->activeGrant = $this->grant;

        $server = ServerFactory::create($this->grant, self::BASE_URI);
        $server->httpRequest = new Request($method, self::BASE_URI . ltrim($path, '/'), $headers, $body ?? '');
        $server->httpRequest->setAbsoluteUrl('http://localhost' . self::BASE_URI . ltrim($path, '/'));
        $server->start();

        return HttpBridge::respond($server->httpResponse, new Response(), $method === 'HEAD');
    }

    /**
     * The body Yii would send: the content, or what the stream yields within its range.
     */
    public static function body(Response $response): string
    {
        if ($response->stream === null) {
            return (string)$response->content;
        }

        if (is_array($response->stream)) {
            [$handle, $begin, $end] = $response->stream;
            fseek($handle, $begin);

            return $end < $begin ? '' : (string)fread($handle, $end - $begin + 1);
        }

        return (string)stream_get_contents($response->stream);
    }
}
