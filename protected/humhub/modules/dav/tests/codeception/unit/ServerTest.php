<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\tests\codeception\unit;

use humhub\components\rateLimit\RateLimiter;
use humhub\modules\dav\components\Mount;
use humhub\modules\dav\events\MountsEvent;
use humhub\modules\dav\Module;
use humhub\modules\dav\tests\codeception\_support\DavTestClient;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\events\ScopesEvent;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\Scope;
use humhub\modules\oauth\Module as OAuthModule;
use humhub\modules\user\models\User;
use RuntimeException;
use Sabre\DAV\Collection;
use Sabre\DAV\FS\Directory;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;
use yii\caching\ArrayCache;
use yii\db\Query;
use yii\helpers\FileHelper;

/**
 * The endpoint once a token authenticated: what a token may reach, the limits, and what the
 * response carries.
 */
class ServerTest extends HumHubDbTestCase
{
    private string $dir;

    protected function _before()
    {
        parent::_before();

        $this->dir = Yii::getAlias('@runtime') . '/dav-test-' . uniqid() . '/test';
        FileHelper::createDirectory($this->dir);
        file_put_contents($this->dir . '/hello.txt', 'Hello, world');
        Module::instance()->tempPath = dirname($this->dir) . '/runtime';

        Event::on(ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, [$this, 'collectScopes']);
        Event::on(Module::class, Module::EVENT_COLLECT_MOUNTS, [$this, 'collectMounts']);
        ScopeRegistry::reset();
    }

    protected function _after()
    {
        Event::off(ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, [$this, 'collectScopes']);
        Event::off(Module::class, Module::EVENT_COLLECT_MOUNTS, [$this, 'collectMounts']);
        ScopeRegistry::reset();
        OAuthModule::instance()->activeGrant = null;
        Module::instance()->tempPath = '@runtime/dav';
        FileHelper::removeDirectory(dirname($this->dir));
        Grant::deleteAll();

        parent::_after();
    }

    public function collectScopes(ScopesEvent $event): void
    {
        foreach (['test:read', 'test:write', 'other:read', 'other:write'] as $id) {
            $event->scopes[] = new Scope($id, $id);
        }
    }

    public function collectMounts(MountsEvent $event): void
    {
        $dir = $this->dir;
        $event->mounts[] = new Mount('test', 'test:read', 'test:write', fn() => new Directory($dir));
        $event->mounts[] = new Mount('other', 'other:read', 'other:write', fn() => new class extends Collection {
            public function getName()
            {
                return 'other';
            }

            public function getChildren()
            {
                throw new RuntimeException('SQLSTATE[42S02]: secret table name');
            }
        });
    }

    private function client(array $scopes): DavTestClient
    {
        return DavTestClient::forUser(User::findOne(['id' => 2]), $scopes);
    }

    public function testTheRootListsTheMountsTheTokenMayRead()
    {
        $response = $this->client(['test:read'])->request('PROPFIND', '', ['Depth' => '1']);

        $this->assertSame(207, $response->statusCode);
        $body = DavTestClient::body($response);
        $this->assertStringContainsString('/dav/test/', $body);
        $this->assertStringNotContainsString('/dav/other/', $body);
    }

    public function testFullReachesEveryMount()
    {
        OAuthModule::instance()->settings->set('patScopes', json_encode([ScopeRegistry::FULL]));
        $client = $this->client([ScopeRegistry::FULL]);

        $body = DavTestClient::body($client->request('PROPFIND', '', ['Depth' => '1']));

        $this->assertStringContainsString('/dav/test/', $body);
        $this->assertStringContainsString('/dav/other/', $body);
        $this->assertSame(201, $client->request('PUT', 'test/new.txt', [], 'new')->statusCode);
    }

    public function testAMountWithoutTheReadScopeIsForbidden()
    {
        $response = $this->client(['test:read'])->request('GET', 'other/anything.txt');

        $this->assertSame(403, $response->statusCode);
        $this->assertStringContainsString('other:read', DavTestClient::body($response));
    }

    public function testAFileIsServedWithHeadersThatKeepABrowserFromRunningIt()
    {
        $response = $this->client(['test:read'])->request('GET', 'test/hello.txt');

        $this->assertSame(200, $response->statusCode);
        $this->assertSame('Hello, world', DavTestClient::body($response));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame("default-src 'none'; sandbox", $response->headers->get('Content-Security-Policy'));
    }

    public function testARangeIsServedFromTheStream()
    {
        $response = $this->client(['test:read'])->request('GET', 'test/hello.txt', ['Range' => 'bytes=7-11']);

        $this->assertSame(206, $response->statusCode);
        $this->assertIsArray($response->stream);
        $this->assertSame('world', DavTestClient::body($response));
    }

    public function testAHeadHasNoBody()
    {
        $response = $this->client(['test:read'])->request('HEAD', 'test/hello.txt');

        $this->assertSame(200, $response->statusCode);
        $this->assertNull($response->stream);
        $this->assertSame('', $response->content);
        $this->assertSame('12', $response->headers->get('Content-Length'));
    }

    public function testWritingNeedsTheWriteScope()
    {
        $this->assertSame(403, $this->client(['test:read'])->request('PUT', 'test/new.txt', [], 'new')->statusCode);
        $this->assertFileDoesNotExist($this->dir . '/new.txt');

        $this->assertSame(201, $this->client(['test:read', 'test:write'])->request('PUT', 'test/new.txt', [], 'new')->statusCode);
        $this->assertSame('new', file_get_contents($this->dir . '/new.txt'));
    }

    public function testACopyNeedsTheWriteScopeOfItsDestination()
    {
        $client = $this->client(['test:read', 'other:read']);

        $response = $client->request('COPY', 'test/hello.txt', ['Destination' => 'http://localhost/dav/other/hello.txt']);

        $this->assertSame(403, $response->statusCode);
        $this->assertStringContainsString('other:write', DavTestClient::body($response));
    }

    public function testAMoveNeedsTheWriteScopeOfItsSource()
    {
        $client = $this->client(['test:read']);

        $response = $client->request('MOVE', 'test/hello.txt', ['Destination' => 'http://localhost/dav/test/moved.txt']);

        $this->assertSame(403, $response->statusCode);
        $this->assertFileExists($this->dir . '/hello.txt');
    }

    public function testPostIsRefused()
    {
        $response = $this->client(['test:read', 'test:write'])->request('POST', 'test/', ['Content-Type' => 'application/x-www-form-urlencoded'], 'sabreAction=mkcol&name=x');

        $this->assertSame(405, $response->statusCode);
        $this->assertDirectoryDoesNotExist($this->dir . '/x');
    }

    public function testAnInternalErrorTellsNothingAboutItself()
    {
        $response = $this->client(['other:read'])->request('PROPFIND', 'other/', ['Depth' => '1']);

        $this->assertSame(500, $response->statusCode);
        $this->assertStringNotContainsString('secret', DavTestClient::body($response));
        $this->assertStringNotContainsString('SQLSTATE', DavTestClient::body($response));
    }

    public function testTheFilesOfTheClientsStayOutOfTheMount()
    {
        $client = $this->client(['test:read', 'test:write']);

        $this->assertSame(201, $client->request('PUT', 'test/._hello.txt', [], 'resource fork')->statusCode);
        $this->assertSame(201, $client->request('PUT', 'test/~$report.docx', [], 'owner')->statusCode);

        $this->assertFileDoesNotExist($this->dir . '/._hello.txt');
        $this->assertFileDoesNotExist($this->dir . '/~$report.docx');
        $this->assertSame('resource fork', DavTestClient::body($client->request('GET', 'test/._hello.txt')));
        // Kept per user: the path names no user.
        $this->assertCount(2, glob(Module::instance()->getTempPath(2) . '/*.tempfile'));
    }

    public function testALockIsKeptAndReleased()
    {
        $client = $this->client(['test:read', 'test:write']);
        $lockBody = '<?xml version="1.0" encoding="utf-8"?><d:lockinfo xmlns:d="DAV:"><d:lockscope><d:exclusive/></d:lockscope>'
            . '<d:locktype><d:write/></d:locktype><d:owner>test</d:owner></d:lockinfo>';

        $response = $client->request('LOCK', 'test/hello.txt', ['Content-Type' => 'application/xml'], $lockBody);

        $this->assertSame(200, $response->statusCode);
        $token = trim((string)$response->headers->get('Lock-Token'), '<>');
        $this->assertNotSame('', $token);
        $this->assertStringStartsWith('opaquelocktoken:', $token);
        $this->assertSame(1, (int)(new Query())->from('dav_lock')->where(['token' => substr($token, strlen('opaquelocktoken:'))])->count());

        // Locked: writing without the token is refused.
        $this->assertSame(423, $client->request('PUT', 'test/hello.txt', [], 'changed')->statusCode);
        $this->assertSame(204, $client->request('PUT', 'test/hello.txt', ['If' => '(<' . $token . '>)'], 'changed')->statusCode);

        $this->assertSame(204, $client->request('UNLOCK', 'test/hello.txt', ['Lock-Token' => '<' . $token . '>'])->statusCode);
        $this->assertSame(0, (int)(new Query())->from('dav_lock')->count());
    }

    public function testTheRateLimitRefusesWithRetryAfter()
    {
        Yii::$app->set('cache', new ArrayCache());
        /** @var RateLimiter $limiter */
        $limiter = Yii::$app->rateLimiter;
        $limiter->policies['dav.*:read'] = ['burst' => ['limit' => 1, 'span' => 60]];
        $client = $this->client(['test:read']);

        $first = $client->request('GET', 'test/hello.txt');
        $second = $client->request('GET', 'test/hello.txt');

        $this->assertSame(200, $first->statusCode);
        $this->assertNotNull($first->headers->get('RateLimit'));
        $this->assertSame(429, $second->statusCode);
        $this->assertGreaterThan(0, (int)$second->headers->get('Retry-After'));
    }

    public function testAMountOfAnInvalidOrTakenNameIsIgnored()
    {
        $handler = static function (MountsEvent $event) {
            $event->mounts[] = new Mount('Test Files', 'test:read', 'test:write', fn() => new Directory('/tmp'));
            $event->mounts[] = new Mount('test', 'test:read', 'test:write', fn() => new Directory('/tmp'));
        };
        Event::on(Module::class, Module::EVENT_COLLECT_MOUNTS, $handler);

        $mounts = Module::instance()->getMounts(User::findOne(['id' => 2]));

        Event::off(Module::class, Module::EVENT_COLLECT_MOUNTS, $handler);
        $this->assertArrayHasKey('other', $mounts);
        $this->assertArrayNotHasKey('Test Files', $mounts);
        // The first one stays: the duplicate would build a collection named "tmp".
        $this->assertSame('test', $mounts['test']->createCollection()->getName());
    }
}
