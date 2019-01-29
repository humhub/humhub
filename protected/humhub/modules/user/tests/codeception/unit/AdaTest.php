<?php

namespace tests\codeception\unit;

use Codeception\Test\Unit;

use humhub\modules\user\authclient\Ada;

class AdaWithFakeApi extends Ada {
        public function api($apiSubUrl, $method = 'GET', $data = [], $headers = []) {
            $this->assertEquals($url,'users/me');
        }
        public function getReturnUrl() {
            return 'returnUrl';
        }
}

class FakeRequest {
    public $headers;
    public $data;
    public function getHeaders() {
        return $this->headers;
    }
    public function setHeaders($headers) {
        $this->headers = $headers;
    }
    public function getData() {
        return $this->data;
    }
    public function setData($data) {
        $this->data = $data;
    }
}

class FakeAccesToken {
    public function getToken() {
        return 'accessToken';
    }
}

class AdaTest extends Unit
{

    public function setUp()
    {
        $this->ada = new Ada();
        $this->fakeAda = new AdaWithFakeApi();
        $this->fakeAda->clientId = 'clientId';
    }

    public function testAutoRefreshAccessTokenIsFalse()
    {
        $this->assertFalse($this->ada->autoRefreshAccessToken);
    }

    public function testAutoExchangeAccessTokenIsFalse()
    {
        $this->assertFalse($this->ada->autoExchangeAccessToken);
    }

    public function testValidateAuthStateIsFalse()
    {
        $this->assertFalse($this->ada->validateAuthState);
    }

    public function testAdaUrlContainsClientIdProperly()
    {
        $uri = $this->fakeAda->buildAuthUrl();
        $this->assertContains('client_id=clientId', $uri);
    }

    public function testAdaUrlContainsResponseTypeProperly()
    {
        $uri = $this->fakeAda->buildAuthUrl();
        $this->assertContains('response_type=code', $uri);
    }

    public function testAdaUrlContainsRedirectUriIdProperly()
    {
        $uri = $this->fakeAda->buildAuthUrl();
        $this->assertContains('redirect_uri=returnUrl', $uri);
    }

    public function testAccessTokenIsPutIntoAuthorizationHeader()
    {
        $accesToken = new FakeAccesToken();
        $request = new FakeRequest();
        $this->ada->applyAccessTokenToRequest($request, $accesToken);
        $this->assertEquals("Basic accessToken", $request->getHeaders()['Authorization']);
    }

}
