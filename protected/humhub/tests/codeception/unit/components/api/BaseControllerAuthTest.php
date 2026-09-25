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
use yii\base\InlineAction;
use yii\web\UnauthorizedHttpException;

/**
 * The authenticator of a controller that ends up without any authentication method - one that
 * does not allow session authentication, on an installation where no module contributes a
 * token method. Yii's `CompositeAuth` skips authentication entirely when it has no method, so
 * such a controller would run every action as a guest, guest access disabled or not.
 *
 * @see BaseController::behaviors()
 */
class BaseControllerAuthTest extends HumHubDbTestCase
{
    public function testControllerWithoutAuthMethodsRejectsTheRequest()
    {
        static::allowGuestAccess(false);

        $this->expectException(UnauthorizedHttpException::class);
        $this->runAuthenticator($this->controllerWithoutAuthMethods());
    }

    /**
     * A browser session is not an authentication method of such a controller, so a logged-in
     * user is rejected as well rather than let through as a guest.
     */
    public function testControllerWithoutAuthMethodsRejectsASessionUser()
    {
        static::allowGuestAccess(false);
        static::becomeUser('Admin');

        $this->expectException(UnauthorizedHttpException::class);
        $this->runAuthenticator($this->controllerWithoutAuthMethods());
    }

    public function testControllerWithoutAuthMethodsRejectsAGuestAllowedActionWithoutGuestAccess()
    {
        static::allowGuestAccess(false);

        $this->expectException(UnauthorizedHttpException::class);
        $this->runAuthenticator($this->controllerWithoutAuthMethods(['index']));
    }

    public function testControllerWithoutAuthMethodsServesAGuestAllowedActionWithGuestAccess()
    {
        static::allowGuestAccess(true);

        $this->assertTrue($this->runAuthenticator($this->controllerWithoutAuthMethods(['index'])));
    }

    private function controllerWithoutAuthMethods(array $guestAllowedActions = []): BaseController
    {
        return new class ('test', Yii::$app, [], $guestAllowedActions) extends BaseController {
            public function __construct($id, $module, $config, array $guestAllowedActions)
            {
                $this->guestAllowedActions = $guestAllowedActions;
                parent::__construct($id, $module, $config);
            }

            public function actionIndex()
            {
                return [];
            }
        };
    }

    private function runAuthenticator(BaseController $controller): bool
    {
        $controller->action = new InlineAction('index', $controller, 'actionIndex');

        return $controller->getBehavior('authenticator')->beforeAction($controller->action);
    }
}
