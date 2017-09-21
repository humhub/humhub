<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 19.09.2017
 * Time: 16:04
 */

namespace humhub\modules\mobile\controllers;


use humhub\modules\user\components\BaseAccountController;

class SettingsController extends BaseAccountController
{
    public function actionIndex()
    {
        return $this->render('device', []);
    }

}