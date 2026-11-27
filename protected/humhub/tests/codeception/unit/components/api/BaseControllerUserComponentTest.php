<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\api;

use humhub\components\api\ApiRules;
use humhub\components\api\BaseController;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InlineAction;

/**
 * The session-less user component an API request runs with replaces the one of the
 * application, it keeps the configured impersonation restrictions.
 *
 * @see BaseController::beforeAction()
 */
class BaseControllerUserComponentTest extends HumHubDbTestCase
{
    public function testUserComponentKeepsTheImpersonationConfig()
    {
        Yii::$app->user->setImpersonation(['allowPrivateContentAccess' => false, 'log' => false]);

        $this->runBeforeAction();

        $this->assertFalse(Yii::$app->user->enableSession);
        $this->assertFalse(Yii::$app->user->getImpersonation()->allowPrivateContentAccess);
        $this->assertFalse(Yii::$app->user->getImpersonation()->log);
    }

    public function testUserComponentKeepsTheDefaultImpersonationConfig()
    {
        $this->runBeforeAction();

        $this->assertFalse(Yii::$app->user->enableSession);
        $this->assertTrue(Yii::$app->user->getImpersonation()->allowPrivateContentAccess);
        $this->assertTrue(Yii::$app->user->getImpersonation()->log);
    }

    private function runBeforeAction(): void
    {
        $controller = new class ('test', Yii::$app) extends BaseController {
            public function behaviors()
            {
                // The authentication is not part of this test
                return [];
            }

            public function actionIndex()
            {
                return [];
            }
        };

        Yii::$app->request->setPathInfo(ApiRules::PREFIX_V2 . 'test');
        Yii::$app->controller = $controller;
        $controller->action = new InlineAction('index', $controller, 'actionIndex');

        $this->assertTrue($controller->beforeAction($controller->action));
    }
}
