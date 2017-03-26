<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use yii\helpers\Url;
use humhub\components\Controller;

/**
 * HomeController redirects to the home page
 *
 * @author luke
 * @since 1.2
 */
class HomeController extends Controller
{

    /**
     * Redirects to the home controller/action
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(Url::home());
    }

}
