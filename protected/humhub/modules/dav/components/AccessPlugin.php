<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use humhub\components\api\ApiRateLimitFilter;
use humhub\components\rateLimit\RateLimiter;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Grant;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\MethodNotAllowed;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Yii;

/**
 * Enforces the scopes of the mounts and the rate limits, before any other plugin handles a
 * request.
 *
 * - A read method (`GET`, `HEAD`, `OPTIONS`, `PROPFIND`, `REPORT`) needs the read scope of the
 *   mount it addresses, any other method its write scope. `COPY` reads its source, `MOVE`
 *   changes it, and both write to the mount of their `Destination`.
 * - `POST` is refused: no plugin of the endpoint takes it, and it is the one method a form of
 *   another site can send with the user's cached credentials.
 * - The request counts once against the policy of the `dav` channel for the scope it needs
 *   (`dav.<scope>`, `dav.*:read|write`, `dav.full`, see `docs/develop/concept-rate-limiting.md`):
 *   the write scope if it writes anywhere, else the read scope; `full` for the root and
 *   unknown mounts.
 *
 * @since 1.20
 */
class AccessPlugin extends ServerPlugin
{
    public const CHANNEL = 'dav';

    public const READ_METHODS = ['GET', 'HEAD', 'OPTIONS', 'PROPFIND', 'REPORT'];

    private ?Server $server = null;

    /**
     * Sub-requests (a `HEAD` runs as `GET`) pass the same event again; they are not counted.
     */
    private bool $counted = false;

    /**
     * @param array<string, Mount> $mounts by name
     */
    public function __construct(
        private readonly Grant $grant,
        private readonly array $mounts,
        private readonly ScopeAccess $access,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function initialize(Server $server)
    {
        $this->server = $server;
        // Ahead of every other plugin (default priority 100).
        $server->on('beforeMethod:*', [$this, 'beforeMethod'], 10);
    }

    /**
     * @inheritdoc
     */
    public function getPluginName()
    {
        return 'humhub-access';
    }

    /**
     * @throws Forbidden when the token lacks a scope
     * @throws MethodNotAllowed for `POST`
     * @throws TooManyRequests when the rate limit is exhausted
     */
    public function beforeMethod(RequestInterface $request, ResponseInterface $response)
    {
        $method = strtoupper($request->getMethod());
        if ($method === 'POST') {
            throw new MethodNotAllowed('POST is not supported.');
        }

        $reads = in_array($method, self::READ_METHODS, true);

        // [path, whether it is changed]
        $targets = [[$request->getPath(), !$reads && $method !== 'COPY']];
        if (in_array($method, ['COPY', 'MOVE'], true) && $request->getHeader('Destination') !== null) {
            $targets[] = [$this->server->calculateUri($request->getHeader('Destination')), true];
        }

        $counted = null;
        foreach ($targets as [$path, $write]) {
            $mount = $this->mounts[self::mountName($path)] ?? null;
            if ($mount === null) {
                continue;
            }

            $required = $write ? $mount->writeScope : $mount->readScope;
            if (!$this->access->allows($required)) {
                throw new Forbidden('The token needs the scope ' . implode(' or ', (array)$required) . '.');
            }

            // The write scope wins: a request that writes anywhere is a write.
            if ($counted === null || $write) {
                $counted = ((array)$required)[0] ?? ScopeRegistry::FULL;
            }
        }

        $this->limit($counted ?? ScopeRegistry::FULL, $response);
    }

    /**
     * The mount a path lies in: its first segment.
     */
    public static function mountName(string $path): string
    {
        return explode('/', trim($path, '/'), 2)[0];
    }

    /**
     * @throws TooManyRequests
     */
    private function limit(string $scope, ResponseInterface $response): void
    {
        if ($this->counted) {
            return;
        }
        $this->counted = true;

        /** @var RateLimiter $limiter */
        $limiter = Yii::$app->rateLimiter;
        $policy = ApiRateLimitFilter::resolvePolicy($limiter, self::CHANNEL, $scope);
        if ($policy === null) {
            return;
        }

        $result = $limiter->hit($policy, 'token:' . $this->grant->id, "WebDAV '" . $this->server->getRequestUri() . "'");
        if (!$result->allowed) {
            throw new TooManyRequests($result);
        }

        foreach ($result->toHeaders() as $name => $value) {
            $response->setHeader($name, $value);
        }
    }
}
