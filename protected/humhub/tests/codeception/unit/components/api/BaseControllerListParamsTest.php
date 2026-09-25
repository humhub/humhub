<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\api;

use humhub\components\api\BaseController;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\filters\auth\QueryParamAuth;

/**
 * The transport parameters {@see BaseController::listParams()} strips: the route parameter,
 * jQuery's cache-buster and the token parameter of every query-parameter authentication method
 * of the controller's authenticator — however that method is configured.
 */
class BaseControllerListParamsTest extends HumHubDbTestCase
{
    public function testStripsTheTokenParameterOfEveryQueryParamAuthConfiguration()
    {
        Yii::$app->request->setQueryParams([
            'r' => 'space/api/space',
            '_' => '123',
            'access-token' => 'a',
            'custom-token' => 'b',
            'array-token' => 'c',
            'object-token' => 'd',
            'q' => 'cal',
        ]);

        $controller = $this->controller([
            // A class string and a `__class` config without `tokenParam`: the subclass's default.
            CustomTokenQueryParamAuth::class,
            ['__class' => CustomTokenQueryParamAuth::class],
            // The base class without `tokenParam`: its default (`access-token`).
            ['class' => QueryParamAuth::class],
            ['class' => QueryParamAuth::class, 'tokenParam' => 'array-token'],
            new QueryParamAuth(['tokenParam' => 'object-token']),
        ]);

        $this->assertSame(['q' => 'cal'], $controller->publicListParams());
    }

    public function testKeepsATokenNameOfNoConfiguredMethod()
    {
        Yii::$app->request->setQueryParams(['access-token' => 'a', 'custom-token' => 'b']);

        $controller = $this->controller([CustomTokenQueryParamAuth::class]);

        $this->assertSame(['access-token' => 'a'], $controller->publicListParams());
    }

    private function controller(array $authMethods): BaseController
    {
        return new class ('test', Yii::$app, [], $authMethods) extends BaseController {
            public function __construct($id, $module, $config, private readonly array $testAuthMethods)
            {
                parent::__construct($id, $module, $config);
            }

            protected function getAuthMethods(): array
            {
                return $this->testAuthMethods;
            }

            public function publicListParams(): array
            {
                return $this->listParams();
            }
        };
    }
}

/**
 * A query-parameter authentication method with a token parameter of its own.
 */
class CustomTokenQueryParamAuth extends QueryParamAuth
{
    public $tokenParam = 'custom-token';
}
