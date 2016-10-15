<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\controllers;


use humhub\components\Controller;


/**
 * Index Controller shows a simple welcome page.
 *
 * @author luke
 */
class IndexController extends Controller
{

    /**
     * Index View just provides a welcome page
     */
    public function actionIndex()
    {
        return $this->render('index', array());
    }

    /**
     * Checks if we need to call SetupController or ConfigController.
     */
    public function actionGo()
    {
        if ($this->module->checkDBConnection()) {
            return $this->redirect(['setup/init']);
        } else {
            return $this->redirect(['setup/prerequisites']);
        }
    }

}
