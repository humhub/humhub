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
 * Description of LoggingController
 * 
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class LoggingController extends Controller
{

    public $subLayout = "/_layout";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {

        $pageSize = 10;

        $criteria = new CDbCriteria();
        //$criteria->condition = 'collumnName1 = :id';
        $criteria->order = 'id DESC';
        //$criteria->params = array(':id' => $id);

        $itemCount = Logging::model()->count($criteria);

        $pagination = new CPagination($itemCount);
        $pagination->setPageSize($pageSize);
        $pagination->applyLimit($criteria);  // the trick is here!

        $logEntries = Logging::model()->findAll($criteria);

        $this->render('index', array(
            'entries' => $logEntries, // must be the same as $item_count
            'itemCount' => $itemCount,
            'pageSize' => $pageSize,
            'pagination' => $pagination,
        ));
    }

    public function actionFlush()
    {
        $this->forcePostRequest();
        Logging::model()->deleteAll();
        $this->redirect($this->createUrl('index'));
    }

}
