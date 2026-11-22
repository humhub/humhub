<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Sabre\DAV\ICollection;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Yii;

/**
 * A `GET` of a folder, as a browser opening `/dav/` sends it, gets a line of text on what the
 * address is for. Sabre itself has no `GET` for a collection (`501 Not Implemented`), and its
 * browser plugin would serve a page with forms on the platform's domain.
 *
 * @since 1.20
 */
final class FolderGetPlugin extends ServerPlugin
{
    private Server $server;

    /**
     * @inheritdoc
     */
    public function initialize(Server $server)
    {
        $this->server = $server;
        // Before Sabre's own handler (100)
        $server->on('method:GET', [$this, 'httpGet'], 90);
    }

    /**
     * @return bool false when answered
     */
    public function httpGet(RequestInterface $request, ResponseInterface $response): bool
    {
        $path = $request->getPath();
        if (!$this->server->tree->nodeExists($path) || !$this->server->tree->getNodeForPath($path) instanceof ICollection) {
            return true;
        }

        $response->setStatus(200);
        $response->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->setBody(Yii::t('DavModule.base', 'This is the WebDAV address of {name}. Connect it as a network drive, e.g. in Finder or Windows Explorer; a browser does not show the files here.', [
            'name' => Yii::$app->name,
        ]));

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPluginName()
    {
        return 'humhub-folder-get';
    }
}
