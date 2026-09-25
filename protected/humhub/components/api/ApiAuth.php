<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\filters\auth\CompositeAuth;

/**
 * The authenticator of {@see BaseController}: Yii's `CompositeAuth`, failing closed when it
 * has no authentication method.
 *
 * `CompositeAuth` skips authentication entirely without a method, which lets every action run
 * as a guest - guest access disabled or not, `$guestAllowedActions` or not. A controller ends
 * up without one when it does not allow session authentication and no module contributes a
 * token method: a token-only endpoint on an installation without the `rest` module, or an
 * endpoint whose session opt-in is misspelled. Such a request is answered with a 401 instead,
 * and actions declared optional (guest access) stay reachable as they are with any method.
 *
 * @since 1.20
 */
class ApiAuth extends CompositeAuth
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (empty($this->authMethods)) {
            Yii::warning(
                'API controller ' . get_class($action->controller) . ' has no authentication method; '
                . 'requests are answered as unauthenticated. Set $allowSessionAuth for an endpoint '
                . 'the platform\'s frontend calls.',
                'api',
            );
        }

        // What CompositeAuth::beforeAction() runs unless it has no method: authenticate, let an
        // optional action through, answer everything else with a 401.
        return AuthMethod::beforeAction($action);
    }
}
