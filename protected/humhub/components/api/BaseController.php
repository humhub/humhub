<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User as UserModel;
use Yii;
use yii\base\Event;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\auth\CompositeAuth;
use yii\web\JsonParser;
use yii\web\NotFoundHttpException;

/**
 * Base class of every HTTP API controller of the platform.
 *
 * API endpoints live next to the module they belong to
 * (`humhub\modules\<module>\controllers\api\`) and declare their routes in that module's
 * `config.php` via {@see ApiRules}. This class provides what all of them share: request and
 * response conventions, the authentication pipeline, and the guards that keep an endpoint
 * from being reachable outside the API URL space.
 *
 * ## Conventions
 *
 * - Responses are JSON; request bodies are parsed as JSON.
 * - Errors are plain HTTP status codes with Yii's JSON error body — there is no
 *   `{code, message}` success/failure envelope. Validation failures are
 *   `422 {"errors": {attribute: [messages]}}`, see {@see self::validationErrors()}.
 * - Timestamps are ISO-8601 with offset, field names are camelCase (see the serializers of
 *   the individual modules).
 *
 * ## Authentication
 *
 * Core ships session authentication ({@see SessionAuth}) only, and a controller must opt in
 * to it via {@see self::$enableSessionAuth}. Additional methods (token, JWT, HTTP Basic …)
 * are contributed by modules through {@see self::EVENT_COLLECT_AUTH_METHODS}, and they apply
 * to every API controller: an installation with such a module can call core endpoints with a
 * token, a core-only installation cannot.
 *
 * Ordering is a contract, not an accident: contributed (token) methods run BEFORE session
 * authentication, so a request carrying a valid token authenticates as the token user even
 * with a session cookie present.
 *
 * Why the opt-in default matters: session authentication deliberately bypasses the API user
 * allowlist a module may enforce for its token methods (the frontend has to work for every
 * logged-in user). Endpoints written for token clients — including third-party ones — may
 * have authorization written for that narrower threat model, so they must not silently
 * become reachable from any logged-in browser session.
 *
 * ## Guards
 *
 * API controllers live in module namespaces that Yii's fallback routing reaches by default
 * (`/comment/api/comment/view` would resolve straight to the action, outside the API prefix
 * and therefore outside CSRF handling, verb constraints and this pipeline; a cross-site
 * top-level navigation can trigger such a GET with the session cookie attached). The
 * `pathInfo` check in {@see self::beforeAction()} is what prevents that — including for
 * query-param routing (`?r=comment/api/comment/view`), where `pathInfo` is empty and the
 * request therefore fails closed. As a consequence the API requires pretty URLs.
 *
 * Subclasses additionally constrain each action to its HTTP methods
 * ({@see \yii\filters\VerbFilter}), so a mutating action can never run on a safe method.
 *
 * @since 1.19
 */
abstract class BaseController extends Controller
{
    /**
     * Modules contribute additional authentication methods (token, JWT, HTTP Basic …) by
     * handling this event and appending method configurations to
     * {@see AuthMethodsEvent::$authMethods}. Contributed methods run before session
     * authentication.
     */
    public const EVENT_COLLECT_AUTH_METHODS = 'collectApiAuthMethods';

    /**
     * @inheritdoc
     *
     * Authentication is handled by the authenticator behavior below, not by the core access
     * control layer.
     */
    public $access = ControllerAccess::class;

    /**
     * @inheritdoc
     *
     * Yii's automatic CSRF validation is off because token requests must be exempt; session
     * requests are CSRF-checked by {@see SessionAuth} instead.
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    protected $doNotInterceptActionIds = ['*'];

    /**
     * @var bool whether this controller accepts browser-session authentication. `false` by
     *      default on purpose — see the class docblock. Core endpoints serving the platform's
     *      own frontend set it to `true`.
     *
     * @internal a module setting this to `true` takes full responsibility for its own
     *           authorization: every logged-in user can then reach the endpoint, regardless
     *           of any API allowlist.
     */
    protected bool $enableSessionAuth = false;

    /**
     * @var string[] ids of actions guests may call without any authentication, mirroring
     *      core's `AccessControl::$guestAllowedActions`. Honored only while guest access is
     *      enabled platform-wide ({@see AuthHelper::isGuestAccessEnabled()}). Requests with
     *      valid credentials still authenticate normally; actions listed here remain
     *      responsible for their own guest-safe authorization (`Content::canView()` etc.).
     */
    protected array $guestAllowedActions = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => CompositeAuth::class,
                'optional' => AuthHelper::isGuestAccessEnabled() ? $this->guestAllowedActions : [],
                'authMethods' => $this->getAuthMethods(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // Hard-fail anything that did not arrive through an API URL rule — see the class
        // docblock's "Guards" section. Must run before authentication.
        if (!str_starts_with(Yii::$app->request->pathInfo, ApiRules::PREFIX_V2)) {
            Yii::$app->response->format = 'json';
            throw new NotFoundHttpException();
        }

        $appUser = Yii::$app->getUser();

        Yii::$app->set('user', array_merge([
            // Always session-less: a token login (`yii\web\User::login()`) must never write
            // into the browser session. SessionAuth restores the session identity through a
            // temporary window instead — see its getSessionIdentity().
            'enableSession' => false,
            // Session-authenticated requests honor the same idle/absolute expiry rules as
            // the web UI (irrelevant for the session-less token methods).
            'authTimeout' => $appUser->authTimeout,
            'absoluteAuthTimeout' => $appUser->absoluteAuthTimeout,
        ], $this->getUserComponentConfig()));

        Yii::$app->response->format = 'json';

        Yii::$app->request->setBodyParams(null);
        Yii::$app->request->enableCsrfCookie = false;
        Yii::$app->request->parsers['application/json'] = JsonParser::class;

        return parent::beforeAction($action);
    }

    /**
     * The authentication methods of this request, in precedence order: everything modules
     * contribute first, session authentication last.
     *
     * @return array method configurations for {@see CompositeAuth::$authMethods}
     */
    protected function getAuthMethods(): array
    {
        $event = new AuthMethodsEvent();
        Event::trigger(self::class, self::EVENT_COLLECT_AUTH_METHODS, $event);

        $authMethods = $event->authMethods;

        if ($this->enableSessionAuth) {
            $authMethods[] = ['class' => SessionAuth::class];
        }

        return $authMethods;
    }

    /**
     * Configuration of the request's user component. A module providing token authentication
     * overrides the identity/user class it needs (e.g. to resolve access tokens) via
     * {@see EVENT_COLLECT_AUTH_METHODS}'s own controller base class.
     */
    protected function getUserComponentConfig(): array
    {
        return [
            'class' => \humhub\modules\user\components\User::class,
            'identityClass' => UserModel::class,
        ];
    }

    /**
     * Answers a failed model validation with `422 {"errors": {attribute: [messages]}}`.
     *
     * Keys are camelCased ({@see Format::attribute()}) so a client can match them against
     * the field names it sent instead of against the column names of a table it never sees.
     *
     * @param \yii\base\Model $model
     * @return array
     */
    protected function validationErrors($model): array
    {
        Yii::$app->response->statusCode = 422;

        $errors = [];
        foreach ($model->getErrors() as $attribute => $messages) {
            $errors[Format::attribute($attribute)] = $messages;
        }

        return ['errors' => $errors];
    }

    /**
     * Applies offset pagination to the given query from the `page`/`pageSize` request
     * parameters and returns the pagination for {@see self::returnPagination()}.
     */
    protected function handlePagination(ActiveQuery $query, int $defaultPageSize = 25, int $maxPageSize = 100): Pagination
    {
        $pageSize = (int)Yii::$app->request->get('pageSize', $defaultPageSize);
        $pageSize = max(1, min($pageSize, $maxPageSize));
        $page = max(1, (int)Yii::$app->request->get('page', 1));

        $pagination = new Pagination(['totalCount' => (clone $query)->count()]);
        $pagination->setPageSize($pageSize);
        $pagination->setPage($page - 1);

        $query->offset($pagination->offset);
        $query->limit($pagination->limit);

        return $pagination;
    }

    /**
     * The paginated list envelope: `{results, total, page, pageSize, pages}`. Clients build
     * their own URLs from `page`/`pageSize`, so no link block is emitted.
     */
    protected function returnPagination(Pagination $pagination, array $results): array
    {
        return [
            'results' => $results,
            'total' => (int)$pagination->totalCount,
            'page' => $pagination->getPage() + 1,
            'pageSize' => $pagination->getPageSize(),
            'pages' => $pagination->getPageCount(),
        ];
    }
}
