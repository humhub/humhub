<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\psr;

use humhub\components\psr\HttpFactory;
use humhub\components\psr\ResponseBridge;
use humhub\components\psr\ServerRequestFactory;
use tests\codeception\_support\HumHubDbTestCase;
use yii\web\Request;
use yii\web\Response;

class PsrBridgeTest extends HumHubDbTestCase
{
    private array $server;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        parent::tearDown();
    }

    public function testServerRequestCarriesMethodUriHeadersAndBodies()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new Request();
        $request->setHostInfo('https://example.org');
        $request->setUrl('/oauth/token?foo=bar');
        $request->setQueryParams(['foo' => 'bar']);
        $request->setRawBody('grant_type=refresh_token&refresh_token=abc');
        $request->setBodyParams(['grant_type' => 'refresh_token', 'refresh_token' => 'abc']);
        $request->getHeaders()->set('Authorization', 'Bearer xyz');

        $psr = ServerRequestFactory::fromYii($request);

        $this->assertSame('POST', $psr->getMethod());
        $this->assertSame('https://example.org/oauth/token?foo=bar', (string)$psr->getUri());
        $this->assertSame('Bearer xyz', $psr->getHeaderLine('Authorization'));
        $this->assertSame(['foo' => 'bar'], $psr->getQueryParams());
        $this->assertSame('refresh_token', $psr->getParsedBody()['grant_type']);
        $this->assertSame('grant_type=refresh_token&refresh_token=abc', (string)$psr->getBody());
    }

    public function testResponseIsCopiedIntoTheYiiResponse()
    {
        $psr = HttpFactory::instance()->createResponse(302)
            ->withHeader('Location', 'com.humhub.app:/oauth/callback?code=x')
            ->withHeader('Cache-Control', 'no-store')
            ->withBody(HttpFactory::instance()->createStream('{"a":1}'));

        $response = ResponseBridge::toYii($psr, new Response());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('com.humhub.app:/oauth/callback?code=x', $response->getHeaders()->get('Location'));
        $this->assertSame('no-store', $response->getHeaders()->get('Cache-Control'));
        $this->assertSame('{"a":1}', $response->content);
        $this->assertSame(Response::FORMAT_RAW, $response->format);
    }
}
