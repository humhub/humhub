<?php

namespace humhub\core\admin\controllers;

use Yii;
use humhub\components\Controller;
use yii\helpers\Url;

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

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'adminOnly' => true
            ]
        ];
    }

    public function actionIndex()
    {
        $pageSize = 10;

        $query = \humhub\core\admin\models\Log::find();
        $query->orderBy('id DESC');

        $countQuery = clone $query;
        $pagination = new \yii\data\Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $pageSize]);
        $query->offset($pagination->offset)->limit($pagination->limit);       
        
        return $this->render('index', array(
                    'logEntries' => $query->all(),
                    'pagination' => $pagination,
        ));
    }

    public function actionFlush()
    {
        $this->forcePostRequest();
        \humhub\core\admin\models\Log::deleteAll();
        $this->redirect(Url::toRoute('index'));
    }

}
