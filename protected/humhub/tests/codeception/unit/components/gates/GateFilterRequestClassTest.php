<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\gates;

use humhub\components\gates\GateFilter;
use humhub\components\gates\RequestClass;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\Request;

/**
 * The request classification that decides gate applicability must depend on the server-side
 * authentication context (stateless token vs. session), never on the client-supplied Accept
 * header — otherwise a session could escape a gate by sending `Accept: application/json`.
 */
class GateFilterRequestClassTest extends HumHubDbTestCase
{
    private function classify(bool $enableSession, array $headers): RequestClass
    {
        Yii::$app->user->enableSession = $enableSession;

        $request = new Request();
        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }
        Yii::$app->set('request', $request);

        $filter = new class extends GateFilter {
            public function classify(): RequestClass
            {
                return $this->getRequestClass();
            }
        };

        return $filter->classify();
    }

    public function testStatelessRequestIsApiRegardlessOfAcceptHeader()
    {
        // REST/CalDAV set enableSession = false; whatever the client accepts, it is API
        $this->assertSame(RequestClass::Api, $this->classify(false, ['Accept' => 'text/html']));
        $this->assertSame(RequestClass::Api, $this->classify(false, ['Accept' => 'application/json']));
    }

    public function testSessionRequestIsNeverApiEvenWithJsonAccept()
    {
        // A cookie-authenticated request cannot downgrade itself to API via the Accept header
        $this->assertSame(RequestClass::FullPage, $this->classify(true, ['Accept' => 'application/json']));
        $this->assertSame(RequestClass::FullPage, $this->classify(true, ['Accept' => '*/*']));
        $this->assertSame(RequestClass::FullPage, $this->classify(true, ['Accept' => 'text/html']));
    }

    public function testSessionXhrIsAjax()
    {
        $this->assertSame(RequestClass::Ajax, $this->classify(true, [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]));
    }
}
