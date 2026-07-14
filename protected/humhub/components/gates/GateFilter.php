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
 * Attached to [[\humhub\components\Controller]] after the access control behavior. It
 * classifies the request by its authentication context (see [[getRequestClass()]]), asks
 * the [[GateManager]] for an intercepting gate and, if one intercepts, answers according
 * to what the client can consume:
 *
 * - browser navigation: 302 to the gate route (a GET target is stored as returnUrl)
 * - AJAX/PJAX: 401 + JSON `{gate, url}`; the `X-Redirect` header lets `yii.js` redirect
 *   the top-level window
 * - anything else (JSON/API clients, sub-resource fetches): 403 + JSON `{gate, message}`
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

        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $gateUrl = Url::to($gate->getRoute());

        // The response shape follows what the client can consume (content negotiation),
        // decided separately from whether the gate applies (getRequestClass()).
        if ($request->getIsAjax() || $request->getIsPjax()) {
            $response->setStatusCode(401);
            $response->format = Response::FORMAT_JSON;
            $response->data = ['gate' => $gate->getId(), 'url' => $gateUrl];
            // yii.js redirects the top-level window on this header (ajaxComplete handler)
            $response->headers->set('X-Redirect', $gateUrl);
        } elseif ($this->acceptsHtml($request)) {
            // Browser navigation — only a GET target is worth returning to afterwards
            if ($request->getIsGet()) {
                Yii::$app->user->setReturnUrl($request->getUrl());
            }
            $response->redirect($gateUrl);
        } else {
            $response->setStatusCode(403);
            $response->format = Response::FORMAT_JSON;
            $response->data = [
                'gate' => $gate->getId(),
                'url' => $gateUrl,
                'message' => 'This action requires completing the "' . $gate->getId() . '" gate first.',
            ];
        }

        return false;
    }

    /**
     * Classifies the current request by its authentication context — not by client-supplied
     * content negotiation, so a gate cannot be escaped by spoofing request headers.
     *
     * A token-/stateless-authenticated request (e.g. REST, CalDAV — the session is disabled
     * server-side) is [[RequestClass::Api]]. A session-authenticated request is always
     * [[RequestClass::Ajax]] or [[RequestClass::FullPage]] and stays subject to the gates,
     * even when it sends, for example, `Accept: application/json`.
     */
    protected function getRequestClass(): RequestClass
    {
        if (!Yii::$app->user->enableSession) {
            return RequestClass::Api;
        }

        if (Yii::$app->request->getIsAjax() || Yii::$app->request->getIsPjax()) {
            return RequestClass::Ajax;
        }

        return RequestClass::FullPage;
    }

    /**
     * Whether the client negotiates an HTML response (or expresses no preference) — a
     * browser navigation that should be answered with a redirect rather than JSON.
     */
    protected function acceptsHtml($request): bool
    {
        $acceptableContentTypes = $request->getAcceptableContentTypes();
        if (empty($acceptableContentTypes)) {
            // No preference expressed — treat as a browser navigation.
            return true;
        }

        foreach ($acceptableContentTypes as $type => $params) {
            if (in_array($type, ['text/html', 'application/xhtml+xml'], true)) {
                return true;
            }
        }

        return false;
    }
}
