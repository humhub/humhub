<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\components\CspReport;
use humhub\components\Response;
use Yii;

/**
 * Endpoint the browser posts Content Security Policy violation reports to.
 *
 * Reports are only logged when a configured header actually points here through
 * {@see Response::REPORT_URI_PLACEHOLDER}; otherwise the endpoint acknowledges and discards,
 * so it cannot be used to write to the log of an installation that never asked for reports.
 *
 * @since 1.20
 */
class CspReportController extends Controller
{
    public const ROUTE = '/csp-report/index';

    /**
     * Reports are sent by the browser without a session, independently of the guest mode setting.
     *
     * @var string
     */
    public $access = ControllerAccess::class;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // The browser posts the report without a CSRF token.
        $this->enableCsrfValidation = false;

        parent::init();
    }

    public function actionIndex(): void
    {
        $response = Yii::$app->response;
        $response->statusCode = 204;

        if (!$response instanceof Response || !$response->isCspReportingEnabled()) {
            return;
        }

        $request = Yii::$app->request;

        foreach (CspReport::parse($request->getRawBody(), $request->get('disposition') === 'report') as $report) {
            $report->log();
        }
    }
}
