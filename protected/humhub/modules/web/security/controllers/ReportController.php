<?php

namespace humhub\modules\web\security\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\web\security\helpers\CspReport;
use humhub\modules\web\security\models\SecuritySettings;
use Yii;

class ReportController extends Controller
{
    /**
     * Reports are sent by the browser without a session, independently of the guest mode setting.
     *
     * @inheritdoc
     */
    protected $access = ControllerAccess::class;

    public function init()
    {
        $this->enableCsrfValidation = false;
    }

    public function actionIndex()
    {
        Yii::$app->response->statusCode = 204;

        if (!SecuritySettings::isReportingEnabled()) {
            return;
        }

        foreach (CspReport::parse(Yii::$app->request->getRawBody()) as $report) {
            $report->log();
        }
    }

}
