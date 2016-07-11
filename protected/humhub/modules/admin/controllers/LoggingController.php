<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\modules\admin\components\Controller;

/**
 * LoggingController provides access to the database logging.
 *
 * @since 0.5
 */
class LoggingController extends Controller
{

    public function init()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Logging'));
        $this->subLayout = '@admin/views/layouts/information';
        return parent::init();
    }

    public function actionIndex()
    {
        $pageSize = 10;

        $query = \humhub\modules\admin\models\Log::find();
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
        \humhub\modules\admin\models\Log::deleteAll();
        return $this->redirect(['index']);
    }

}
