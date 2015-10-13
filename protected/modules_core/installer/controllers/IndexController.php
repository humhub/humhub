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

/**
 * Index Controller shows a simple welcome page.
 *
 * @author luke
 */
class IndexController extends Controller
{

    /**
     *
     * @var String layout to use
     */
    public $layout = '_layout';

    /**
     * Index View just provides a welcome page
     */
    public function actionIndex()
    {
        $this->render('index', array());
    }

    /**
     * Checks if we need to call SetupController or ConfigController.
     */
    public function actionGo()
    {
        if ($this->getModule()->checkDBConnection()) {
            $this->redirect(Yii::app()->createUrl('//installer/setup/init'));
        } else {
            $this->redirect(Yii::app()->createUrl('//installer/setup/prerequisites'));
        }
    }
}
