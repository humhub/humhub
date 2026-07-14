<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

use humhub\components\InstallationState;
use Yii;
use yii\base\ActionFilter;
use yii\helpers\Url;
use yii\web\Application as WebApplication;
use yii\web\Response;

/**
 * Enforces user gates on controller actions (see `docs/develop/user-gates.md`).
 *
 * Attached to [[\humhub\components\Controller]] after the access control behavior.
 * Classifies the request, asks the [[GateManager]] for an intercepting gate and answers
 * according to the request class:
 *
 * - full page navigation: 302 to the gate route (the original URL is stored as returnUrl)
 * - AJAX/PJAX: 401 + JSON `{gate, url}`; the `X-Redirect` header lets `yii.js` redirect
 *   the top-level window
 * - API: 403 + JSON `{gate, message}`
 *
 * At most one gate intercepts per request. When a gate intercepts, the event is marked
 * as handled so that legacy `EVENT_BEFORE_ACTION` interceptors do not overwrite the
 * response.
 *
 * @since 1.19
 */
class GateFilter extends ActionFilter
{
    /**
     * Routes every page depends on to render — including a gate's own page. They deliver
     * no user content and are therefore never intercepted by any gate: a gate page
     * requesting them would otherwise be answered with a redirect to itself and reload
     * in an endless loop (yii.js navigates on the X-Redirect header).
     */
    private const INFRASTRUCTURE_ROUTES = ['i18n/translations'];

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Yii::$app instanceof WebApplication) {
            return true;
        }

        if (!Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            return true;
        }

        if (in_array($action->controller->route, self::INFRASTRUCTURE_ROUTES, true)) {
            return true;
        }

        $requestClass = $this->getRequestClass();
        $gate = Yii::$app->gateManager->findOpenGate($requestClass, $action->controller->route);

        if ($gate === null) {
            return true;
        }

        $gate->onIntercept();

        $response = Yii::$app->response;
        $gateUrl = Url::to($gate->getRoute());

        switch ($requestClass) {
            case RequestClass::FullPage:
                if (Yii::$app->request->getIsGet()) {
                    Yii::$app->user->setReturnUrl(Yii::$app->request->getUrl());
                }
                $response->redirect($gateUrl);
                break;

            case RequestClass::Ajax:
                $response->setStatusCode(401);
                $response->format = Response::FORMAT_JSON;
                $response->data = ['gate' => $gate->getId(), 'url' => $gateUrl];
                // yii.js redirects the top-level window on this header (ajaxComplete handler)
                $response->headers->set('X-Redirect', $gateUrl);
                break;

            case RequestClass::Api:
                $response->setStatusCode(403);
                $response->format = Response::FORMAT_JSON;
                $response->data = [
                    'gate' => $gate->getId(),
                    'message' => 'This action requires completing the "' . $gate->getId() . '" gate first.',
                ];
                break;
        }

        return false;
    }

    /**
     * Classifies the current request (see [[RequestClass]]).
     *
     * Only requests explicitly negotiating an HTML response are browser navigations —
     * everything else (wildcard or JSON accept headers: service worker and manifest
     * fetches, API clients, curl) must never be answered with a redirect, and especially
     * must not poison the returnUrl that full-page interception stores.
     */
    protected function getRequestClass(): RequestClass
    {
        $request = Yii::$app->request;

        if ($request->getIsAjax() || $request->getIsPjax()) {
            return RequestClass::Ajax;
        }

        // No Accept header at all = no preference expressed; treat as navigation.
        $acceptableContentTypes = $request->getAcceptableContentTypes();
        if (empty($acceptableContentTypes)) {
            return RequestClass::FullPage;
        }

        foreach ($acceptableContentTypes as $type => $params) {
            if (in_array($type, ['text/html', 'application/xhtml+xml'], true)) {
                return RequestClass::FullPage;
            }
        }

        return RequestClass::Api;
    }
}
