<?php

namespace humhub\modules\security\controllers;

use humhub\components\Controller;
use Yii;

class ReportController extends Controller
{
    public function init()
    {
        $this->enableCsrfValidation = false;
    }

    public function actionIndex()
    {
        $json_data = file_get_contents('php://input');
        if ($json_data = json_decode($json_data)) {
            $json_data = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            Yii::error($json_data);
        }
        Yii::$app->response->statusCode = 204;
    }

}