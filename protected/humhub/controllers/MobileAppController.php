<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\Controller;
use humhub\helpers\MobileAppHelper;
use Yii;
use yii\helpers\Url;

/**
 * @since 1.18.0
 */
class MobileAppController extends Controller
{
    public function actionInstanceOpener()
    {
        MobileAppHelper::registerShowOpenerScript();
        Yii::$app->view->registerJs('window.location.href = "' . Url::home() . '";');
        return $this->renderContent('');
    }
}
