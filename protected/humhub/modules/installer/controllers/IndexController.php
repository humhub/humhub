<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\installer\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use yii\helpers\Url;

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
            return $this->redirect(Url::to(['setup/init']));
        } else {
            return $this->redirect(Url::to(['setup/prerequisites']));
        }
    }

}
