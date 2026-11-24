<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use humhub\modules\dav\Module;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use Sabre\DAV\Exception as DavException;
use Sabre\DAV\Exception\NotImplemented;
use Sabre\DAV\Locks\Plugin as LocksPlugin;
use Sabre\DAV\Server;
use Throwable;
use Yii;

/**
 * The Sabre server of one request: the mounts of the user under a [[RootCollection]], the
 * scope and rate checks ([[AccessPlugin]]), locks ([[LockBackend]]) and the filter for the
 * clients' own files ([[TemporaryFilePlugin]]).
 *
 * @since 1.20
 */
final class ServerFactory
{
    /**
     * @param string $baseUri the path of `/dav/`, with a trailing slash
     */
    public static function create(Grant $grant, string $baseUri): Server
    {
        $module = Module::instance();
        $user = $grant->user;

        $mounts = $module->getMounts($user);
        $access = new ScopeAccess((new GrantService())->getEffectiveScopes($grant));

        Server::$exposeVersion = false;

        $server = new Server(new RootCollection($mounts, $access), new NullSapi());
        $server->setBaseUri($baseUri);
        $server->addPlugin(new AccessPlugin($grant, $mounts, $access));
        $server->addPlugin(new LocksPlugin(new LockBackend(Yii::$app->db)));
        $server->addPlugin(new TemporaryFilePlugin($module->getTempPath((int)$user->id)));
        $server->addPlugin(new FolderGetPlugin());
        $server->addPlugin(new Win32PropertiesPlugin());

        // Sabre answers every exception itself; one that is not a DAV status is an error. A
        // method or report no plugin implements is the client's choice, not a fault.
        $server->on('exception', static function (Throwable $e) {
            if (!$e instanceof DavException || ($e->getHTTPCode() >= 500 && !$e instanceof NotImplemented)) {
                Yii::error($e, 'dav');
            }
        });

        return $server;
    }
}
