<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\components\gates\RequestClass;
use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;
use yii\helpers\Url;
use yii\web\Response;

/**
 * Protects actions with the sudo mode (docs/develop/concept-sudo-mode.md): a request of a user whose sudo mode is
 * active passes and refreshes it; otherwise the user is sent to the confirmation. A full page request is redirected
 * to the confirmation page, an AJAX or pjax request and a request to a JSON endpoint get a 401 with the confirmation
 * URL in the `X-Sudo-Required` header, which `humhub.ui.sudo` turns into the dialog. Guests pass (access control
 * answers them), token-authenticated API requests are never intercepted.
 *
 * ```php
 * public function behaviors()
 * {
 *     return array_merge(parent::behaviors(), [
 *         'sudo' => ['class' => SudoFilter::class, 'only' => ['delete']],
 *     ]);
 * }
 * ```
 *
 * @since 1.20
 */
class SudoFilter extends ActionFilter
{
    public const HEADER = 'X-Sudo-Required';

    /**
     * @var callable|null narrows the filter to the requests it returns true for, e.g. a form post that changes the
     * username but not one that only changes other settings; it gets the action
     * @since 1.20
     */
    public $when = null;

    /**
     * @inheritdoc
     */
    protected function isActive($action)
    {
        return parent::isActive($action) && ($this->when === null || call_user_func($this->when, $action));
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $sudo = SudoMode::forCurrentUser();
        $requestClass = RequestClass::current();

        if ($sudo === null || $requestClass === RequestClass::Api) {
            return true;
        }

        if ($sudo->isActive()) {
            $sudo->touch();
            return true;
        }

        // While impersonating there is nobody who could confirm: the action is refused
        if (Yii::$app->user->getImpersonation()->isActive()) {
            throw new ForbiddenHttpException(Yii::t('UserModule.auth', 'This action is not available while impersonating another user.'));
        }

        $confirmUrl = Url::to(['/user/sudo/confirm', 'returnUrl' => $this->getReturnUrl()]);
        $response = Yii::$app->response;

        // A JSON endpoint (the API with the browser session) is never answered with a page
        if ($requestClass === RequestClass::Ajax || $response->format === Response::FORMAT_JSON) {
            $response->setStatusCode(401);
            $response->format = Response::FORMAT_JSON;
            $response->headers->set(self::HEADER, $confirmUrl);
            $response->data = ['sudo' => true, 'url' => $confirmUrl];
            return false;
        }

        $response->redirect($confirmUrl);
        return false;
    }

    /**
     * @return string where to continue after the confirmation: the requested page, or for a form posted without
     * JavaScript the page it came from
     */
    private function getReturnUrl(): string
    {
        $request = Yii::$app->request;
        if ($request->getIsGet()) {
            return $request->getUrl();
        }

        $referrer = $request->getReferrer();
        if ($referrer !== null && str_starts_with($referrer, $request->getHostInfo())) {
            return substr($referrer, strlen($request->getHostInfo())) ?: Url::home();
        }

        return Url::home();
    }
}
