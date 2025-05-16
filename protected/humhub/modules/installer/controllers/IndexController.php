<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\controllers;

use humhub\components\InstallationState;
use Yii;
use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\installer\libs\DynamicConfig;

/**
 * Index Controller shows a simple welcome page.
 *
 * @author luke
 */
class IndexController extends Controller
{
    /**
     * Allow guest access independently from guest mode setting.
     *
     * @var string
     */
    public $access = ControllerAccess::class;

    /**
     * Index View just provides a welcome page
     */
    public function actionIndex()
    {
        return $this->render('index', []);
    }

    /**
     * Checks if we need to call SetupController or ConfigController.
     */
    public function actionGo()
    {
        if (Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            return $this->redirect(['setup/finalize']);
        } else {
            return $this->redirect(['setup/prerequisites']);
        }
    }

}
