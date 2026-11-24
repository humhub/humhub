<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Sabre\DAV\PropPatch;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;

/**
 * Confirms the properties Windows Explorer sets after an upload (`Win32CreationTime`,
 * `Win32LastModifiedTime`, `Win32FileAttributes` …) without storing them: Windows takes a
 * refusal for a failed copy, and nothing on the server reads them. Every other property is
 * still refused (`403`): a client setting it would expect to read it back.
 *
 * @since 1.20
 */
final class Win32PropertiesPlugin extends ServerPlugin
{
    public const NAMESPACE = '{urn:schemas-microsoft-com:}';

    /**
     * @inheritdoc
     */
    public function initialize(Server $server)
    {
        $server->on('propPatch', [$this, 'propPatch']);
    }

    public function propPatch(string $path, PropPatch $propPatch): void
    {
        $names = array_filter(
            array_keys($propPatch->getMutations()),
            static fn(string $name) => str_starts_with($name, self::NAMESPACE . 'Win32'),
        );

        if ($names !== []) {
            $propPatch->handle(array_values($names), static fn() => true);
        }
    }

    /**
     * @inheritdoc
     */
    public function getPluginName()
    {
        return 'humhub-win32-properties';
    }
}
