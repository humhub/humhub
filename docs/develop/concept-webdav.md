# WebDAV

**Since 1.20.** Core serves a WebDAV endpoint at `/dav/` in the core module `dav`
(`humhub\modules\dav\`), built on [`sabre/dav`](https://sabre.io/dav/) ^4.7. Core provides the
endpoint, the authentication, the locks and the limits. The content comes from modules: each
one mounts a collection under a name of its own, e.g. the files module (`cfiles`) under
`/dav/files/`.

A user connects Finder, Windows Explorer, a Linux file manager or any WebDAV client to
`https://<host>/dav/` (or directly to a mount, `https://<host>/dav/files/`).

## Authentication

HTTP Basic, with a [personal access token](concept-oauth.md#personal-access-tokens) as the
password:

- **Username:** the user's username or e-mail address, in any case.
- **Password:** a token (`hhpat_…`). The account password is never accepted. Not every user
  has one (SSO, LDAP), and a token can be limited by scopes and revoked on its own.

The token must be usable, as for the API (`GrantService::isUsable()`):

- not expired;
- its user enabled;
- *Allow personal access tokens* switched on.

So without personal access tokens there is no WebDAV.

A failed authentication answers `401` with `WWW-Authenticate: Basic realm="<site name>"`.

The request is session-less. The user component is replaced by one with
`enableSession = false`, the identity is set without a login, and the grant is published as
`oauth\Module::$activeGrant`. As for token requests of the API, the request class is
`RequestClass::Api`, so only the gates that apply to API requests intercept (forced password
change, maintenance mode).

## Mounts

A module offers a collection in `Module::EVENT_COLLECT_MOUNTS`:

```php
// config.php
[\humhub\modules\dav\Module::class, \humhub\modules\dav\Module::EVENT_COLLECT_MOUNTS, [Events::class, 'onDavCollectMounts']],
```

```php
public static function onDavCollectMounts(MountsEvent $event): void
{
    $user = $event->user;
    $event->mounts[] = new Mount(
        'files',                 // the path segment: /dav/files/
        'cfiles:read',           // the scope for GET, HEAD, OPTIONS, PROPFIND, REPORT
        'cfiles:write',          // the scope for every other method
        static fn() => new FilesCollection($user),
    );
}
```

- **Name.** One path segment of lower-case letters, digits and hyphens. An invalid or taken
  name is ignored and logged.
- **Collection.** A `Sabre\DAV\ICollection`, built only for a request that reaches the mount.
  Its `getName()` must be the mount's name.
- **Scopes.** The module's own [OAuth scopes](concept-oauth.md#scopes), see below.
- **Permissions.** The collection decides what the user may see and change, by the rules of
  the module's web interface. A scope is an upper bound, never a permission.

The root `/dav/` lists the mounts whose read scope the token holds. Nothing can be created
there.

### Connecting a device

The user gets the address and the password where the module's content is, not from a page of
the endpoint: the module offers a [device connection](concept-oauth.md#device-connections) for
its mount, whose dialog shows the address and the username and creates a token with the
mount's scopes. `Module::getMountUrl()` is the address of a mount:

```php
public static function onCollectDeviceConnections(DeviceConnectionsEvent $event): void
{
    $event->connections[] = new DeviceConnection(
        'cfiles-webdav',
        Yii::t('CfilesModule.base', 'Connect as network drive'),
        Yii::t('CfilesModule.base', 'Your files in Finder, Windows Explorer or any WebDAV client.'),
        Yii::t('CfilesModule.base', 'WebDAV – Files'),
        ['cfiles:read', 'cfiles:write'],
        [
            Yii::t('CfilesModule.base', 'Address') => DavModule::instance()->getMountUrl('files'),
            Yii::t('CfilesModule.base', 'Username') => $event->user->username,
        ],
    );
}
```

The module links the dialog (`DeviceConnection::getUrl()`, `ui.modal.load`) from its pages.

## Scopes

A plugin of the server (`AccessPlugin`) checks every request before any other plugin:

| Request | Needs |
|---|---|
| `GET`, `HEAD`, `OPTIONS`, `PROPFIND`, `REPORT` | the read scope of the mount |
| any other method | the write scope of the mount |
| `COPY` | the read scope of the source, the write scope of the `Destination`'s mount |
| `MOVE` | the write scope of both |
| `POST` | refused (`405`) |

`full` satisfies every scope. A missing scope answers `403` and names the scope.

`POST` is refused because no plugin of the endpoint handles it. It is also the one method a
form of another site can send with the credentials a browser has cached for the endpoint.
For the same reason the method is taken from the request line: Yii's `getMethod()` also takes
it from a `_method` parameter and the `X-Http-Method-Override` header.

## Rate limiting

The requests count against the [rate limiter](concept-rate-limiting.md) on their own channel
`dav`, per grant (`token:<grant id>`). The policy is resolved like that of an API action:

1. `dav.<scope>`;
2. `dav.*:read` or `dav.*:write`;
3. `dav.full`.

The scope is the write scope if the request writes anywhere, otherwise the read scope. The
root and unknown mounts count as `full`. A request counts once, also when Sabre runs it again
internally (a `HEAD` runs as `GET`).

| Policy | Burst | Long |
|---|---|---|
| `dav.*:read` | 3000 / minute | 50000 / hour |
| `dav.*:write` | 1200 / minute | 20000 / hour |
| `dav.full` | 3000 / minute | 50000 / hour |

The limits are far above those of the API. WebDAV clients are chatty: a `PROPFIND` per folder,
and `LOCK`, `PUT`, `PROPPATCH` and `UNLOCK` per file copied. They do not retry on `429`, so a
copy that hits the limit fails. A refused request answers `429` with `Retry-After` and the
`RateLimit` headers.

## Locks

Clients lock a file while they write it. Finder and Microsoft Office mount read-only without
locks. Sabre's lock plugin keeps the locks in the table `dav_lock`, shared by every node of a
cluster, in the schema of Sabre's PDO backend (`LockBackend`):

- `uri` and `token` are binary: a lock on `Report.docx` must not match `report.docx`;
- Sabre gives a lock 30 minutes;
- the daily cron removes expired locks.

## The clients' own files

Clients write files for themselves next to the user's files:

- Finder's `._*` and `.DS_Store`;
- Windows' `desktop.ini` and `Thumbs.db`;
- editor swap files;
- the owner files of Microsoft Office (`~$*`);
- the lock files of LibreOffice (`.~lock.*#`).

`TemporaryFilePlugin` (Sabre's `TemporaryFileFilterPlugin` with the last two patterns added)
keeps them out of the mounts. They are stored in `Module::$tempPath` (`@runtime/dav`), in a
folder per user, so a client finds them again. They never become content. The folder is per
user because the path of such a file names a space, not a user.

The daily cron removes those older than `Module::$tempFileLifetime` (a day).

## Responses

`HttpBridge` hands Yii's request to Sabre and Sabre's response back to Yii, which sends it
like any other:

- **No globals.** Sabre never reads `$_SERVER` or writes output itself (`NullSapi`).
- **Streams.** A body that is a stream stays a stream, and only its `Content-Length` bytes
  are sent. A file, and a range of it (`206`), is never read into memory.
- **`HEAD`.** The response has no body.
- **Security headers.** Every response carries `X-Content-Type-Options: nosniff` and
  `Content-Security-Policy: default-src 'none'; sandbox`. A file is served from the platform's
  origin, so an uploaded HTML file must not run its scripts there.
- **Server errors.** A `5xx` says no more than its status. Sabre would put the exception's
  message into the body, whatever it was thrown with (SQL included). The exception is logged
  (category `dav`), except a `501` for a method or report no plugin implements.
- **Properties.** No dead property is stored. The `Win32…` properties Windows Explorer sets
  after an upload are confirmed (`200`, `Win32PropertiesPlugin`), since Windows takes a refusal
  for a failed copy; any other `PROPPATCH` is refused (`403`).
- **`GET` of a folder.** A browser opening `/dav/` gets a line of text on what the address is
  for (`FolderGetPlugin`), not Sabre's `501`.
- **Entry script.** A client that addresses `/index.php/dav/` is served too
  (`ServerController::requestBaseUri()`); the dialogs show the address without it.
- **Version.** Sabre's version is not exposed.

## Storing uploads

`UploadedBody` turns the body of a `PUT` into a temporary file and checks it like an upload of
the web interface (`FileValidator`):

- the allowed file extensions;
- the maximum file size;
- the characters of the name.

It reads only one byte beyond the size limit. A name the platform would change on upload
(`fileNameValidationPattern`) is refused rather than changed, since a client finds its file by
the name it chose.

`File::setStoredFileByPath()` (new) stores a file from a path on the local disk as a new
version, streamed. `setStoredFile()` with an `UploadedFile` stores only real HTTP uploads.

```php
$body = UploadedBody::fromData($data, $name); // throws Sabre\DAV\Exception\Forbidden
$baseFile->setStoredFileByPath($body->path);
```

## Web server

- **Methods.** The web server must pass every method to `index.php`, including `PROPFIND`,
  `MKCOL`, `MOVE`, `COPY`, `LOCK` and `UNLOCK`. The rewrite rules of the shipped configurations
  do.
- **`Authorization`.** Behind Apache with PHP-FPM or CGI, the header must be passed on, as for
  the API.
- **URLs.** Pretty URLs are required.

## Testing

`humhub\modules\dav\tests\codeception\_support\DavTestClient` runs requests in-process, as the
endpoint does once a token has authenticated. Modules use it for their mounts:

```php
$client = DavTestClient::forUser(User::findOne(['id' => 2]), ['cfiles:read', 'cfiles:write']);
$response = $client->request('PUT', 'files/space-1/notes.txt', [], 'my notes');
$this->assertSame(201, $response->statusCode);
```

## Changes

- New `protected/humhub/modules/dav/`, with the migration of `dav_lock`.
- `sabre/dav` ^4.7 in `composer.json`.
- `file\models\File::setStoredFileByPath()`.
- `config/common.php`: the `dav.*` policies of the `rateLimiter`.
- `oauth`: `maxPersonalAccessTokensPerUser` defaults to 10 instead of 3, a token per device;
  [device connections](concept-oauth.md#device-connections) give a device its token.
