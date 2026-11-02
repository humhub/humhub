<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\gates;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Url;

/**
 * A user gate page must never be used as the return URL, otherwise the gate page
 * redirects to itself after the gate is closed (endless redirect loop).
 */
class GateReturnUrlTest extends HumHubDbTestCase
{
    public $fixtureConfig = [
        'default',
    ];

    protected function _before()
    {
        parent::_before();
        Yii::$app->gateManager->register(new FakeGate(['id' => 'return-url-test', 'route' => ['/mygate/check']]));
    }

    protected function _after()
    {
        Yii::$app->gateManager->deregister('return-url-test');
        Yii::$app->session->remove(Yii::$app->user->returnUrlParam);
        parent::_after();
    }

    public function testGateUrlIsNotStoredAsReturnUrl()
    {
        Yii::$app->user->setReturnUrl(Url::to(['/mygate/check']));

        $this->assertNull(Yii::$app->session->get(Yii::$app->user->returnUrlParam));
        $this->assertEquals(Yii::$app->getHomeUrl(), Yii::$app->user->getReturnUrl());
    }

    public function testGateUrlDropsPreviouslyStoredReturnUrl()
    {
        Yii::$app->user->setReturnUrl(Url::to(['/dashboard/dashboard']));
        Yii::$app->user->setReturnUrl(Url::to(['/mygate/check']));

        $this->assertEquals(Yii::$app->getHomeUrl(), Yii::$app->user->getReturnUrl());
    }

    public function testRegularUrlIsStillStoredAsReturnUrl()
    {
        $url = Url::to(['/dashboard/dashboard']);
        Yii::$app->user->setReturnUrl($url);

        $this->assertEquals($url, Yii::$app->user->getReturnUrl());
    }

    public function testStoredGateUrlFallsBackToHomeUrl()
    {
        // e.g. stored by a session that predates the filter in setReturnUrl()
        Yii::$app->session->set(Yii::$app->user->returnUrlParam, Url::to(['/mygate/check']));

        $this->assertEquals(Yii::$app->getHomeUrl(), Yii::$app->user->getReturnUrl());
    }

    public function testStoredGateUrlFallsBackToDefaultUrl()
    {
        Yii::$app->session->set(Yii::$app->user->returnUrlParam, Url::to(['/mygate/check']));

        $this->assertEquals(Url::to(['/dashboard/dashboard']), Yii::$app->user->getReturnUrl(['/dashboard/dashboard']));
        // A gate URL is no valid default either
        $this->assertEquals(Yii::$app->getHomeUrl(), Yii::$app->user->getReturnUrl(['/mygate/check']));
    }

    /**
     * A `returnUrl` configured on the `user` component is applied while the component is still
     * being created, i.e. before `Yii::$app->user` exists. The gate check must not read
     * `Yii::$app->user` in that state (`MustChangePasswordGate::getRoute()` does), otherwise PHP
     * answers the re-entrant `__get()` with "Undefined property: Application::$user".
     */
    public function testReturnUrlConfiguredOnUserComponentDoesNotRecurse()
    {
        $user = Yii::$app->user;
        $url = Url::to(['/dashboard/dashboard']);

        try {
            Yii::$app->set('user', array_merge(Yii::$app->getComponents()['user'], ['returnUrl' => $url]));

            $this->assertEquals($url, Yii::$app->user->getReturnUrl());
        } finally {
            Yii::$app->set('user', $user);
        }
    }
}
