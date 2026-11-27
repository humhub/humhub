<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\health\HealthLevel;
use humhub\components\WithoutInstallationCheck;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Health check endpoints for load balancers, container orchestrators and monitoring.
 *
 * - `GET /health/live`, `GET /health/ready`: without token a minimal answer (`format`, `status`, `state`),
 *   with a valid token the full report.
 * - `GET /health`: the full report of all checks, token required.
 *
 * The token is the component setting `health.token` (`Authorization: Bearer`). An invalid token is always a 401,
 * never a quiet downgrade to the minimal answer. A plain yii\web\Controller on purpose: no session, no access
 * control, no gates, no maintenance mode.
 *
 * @since 1.20
 */
#[WithoutInstallationCheck]
class HealthController extends Controller
{
    public const RATE_LIMIT_POLICY = 'health.auth';

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    '*' => ['GET', 'HEAD'],
                ],
            ],
        ];
    }

    public function actionIndex(): Response
    {
        return $this->respond(HealthLevel::All);
    }

    public function actionLive(): Response
    {
        return $this->respond(HealthLevel::Live);
    }

    public function actionReady(): Response
    {
        return $this->respond(HealthLevel::Ready);
    }

    private function respond(HealthLevel $level): Response
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->set('X-Robots-Tag', 'noindex');

        $health = Yii::$app->health;
        $header = Yii::$app->request->headers->get('Authorization');
        $detailed = false;

        // Only the Bearer scheme is a token attempt; other schemes (e.g. Basic of a web server) count as no token.
        if ($header !== null && preg_match('/^Bearer(\s|$)/i', $header)) {
            if ($health->token === null || $health->token === '') {
                Yii::warning('Health check token sent, but no health token is configured.', 'health');
            }

            // A valid token always passes, so failed attempts of others (e.g. behind a shared proxy IP) cannot
            // lock out monitoring. Only failed attempts are blocked.
            if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $matches) || !$health->validateToken($matches[1])) {
                if ($this->isBlocked()) {
                    $response->statusCode = 429;
                    $response->data = ['error' => 'too many failed attempts'];
                    return $response;
                }

                $this->countFailedAttempt();
                return $this->unauthorized($response);
            }

            $detailed = true;
        } elseif ($level === HealthLevel::All) {
            return $this->unauthorized($response);
        }

        $report = $health->run($level);
        $response->statusCode = $report->getHttpStatusCode();
        $response->data = $report->toArray($detailed);

        return $response;
    }

    private function unauthorized(Response $response): Response
    {
        $response->statusCode = 401;
        $response->headers->set('WWW-Authenticate', 'Bearer');
        $response->data = ['error' => 'unauthorized'];

        return $response;
    }

    private function blockKey(): string
    {
        return 'health:blocked:' . md5((string)Yii::$app->request->getUserIP());
    }

    private function isBlocked(): bool
    {
        try {
            return Yii::$app->cache->get($this->blockKey()) !== false;
        } catch (\Throwable) {
            return false;
        }
    }

    private function countFailedAttempt(): void
    {
        $result = Yii::$app->rateLimiter->hit(
            self::RATE_LIMIT_POLICY,
            (string)Yii::$app->request->getUserIP(),
            "route 'health/" . $this->action->id . "'",
        );

        if (!$result->allowed) {
            try {
                Yii::$app->cache->set($this->blockKey(), true, max(1, (int)$result->retryAfter));
            } catch (\Throwable) {
                // without a cache the rate limiter still logs the refusals
            }
        }
    }
}
