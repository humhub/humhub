<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use yii\helpers\Url;

/**
 * What a module offers a user for connecting a device, e.g. a network drive or a calendar app:
 * the values the device asks for (address, username) and a personal access token as its
 * password, created in a dialog ([[getUrl()]]) with the scopes of the connection.
 *
 * Such a token is an ordinary personal access token, listed and revoked with the others, but
 * the module decides what it does: it needs neither the group permission to create tokens nor
 * the scopes for tokens, only that tokens are allowed. The module offers the connection only
 * while it is switched on. See `DeviceConnectionRegistry` for how a module offers one.
 *
 * @since 1.20
 */
final class DeviceConnection
{
    /**
     * Lower-case letters and digits in words joined by hyphens, e.g. `cfiles-webdav`.
     */
    public const ID_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    /**
     * @param string $id unique, also the issuer of its tokens (`Grant::$issuer`), see [[ID_PATTERN]]
     * @param string $title the dialog's title, e.g. "Connect as network drive"
     * @param string $description what the device gets and how to set it up, a sentence or two
     * @param string $tokenName the name a token gets unless the user changes it, e.g. "WebDAV – Files"
     * @param string[] $scopes the scopes of its tokens
     * @param array<string, string> $fields the values the device asks for, by their label, each
     * shown with a copy button, e.g. `['Address' => 'https://…/dav/files/', 'Username' => 'jane']`
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $tokenName,
        public readonly array $scopes,
        public readonly array $fields,
    ) {
    }

    /**
     * The dialog that shows the fields and creates the token, for `ui.modal.load`.
     */
    public function getUrl(): string
    {
        return Url::to(['/oauth/connect/index', 'id' => $this->id]);
    }
}
