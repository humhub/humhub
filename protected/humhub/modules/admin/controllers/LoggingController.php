<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\models\forms\LogFilterForm;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\admin\models\Log;

/**
 * LoggingController provides access to the database logging.
 *
 * @since 0.5
 */
class LoggingController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    public function init()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Logging'));
        $this->subLayout = '@admin/views/layouts/information';

		return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => SeeAdminInformation::class]
        ];
    }

    public function actionIndex()
    {
        $filter = new LogFilterForm();

        if(Yii::$app->request->post()) {
            $filter->load(Yii::$app->request->post());
        } else {
            $filter->load(Yii::$app->request->get());
        }

        $params = [
            'filter' => $filter,
            'logEntries' => $filter->findEntries(),
            'pagination' => $filter->getPagination(),
        ];

        if(Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            return $this->asJson([
                'html' => $this->renderPartial('log_entries', $params),
                'url' => $filter->getUrl()
            ]);
        }

        return $this->render('index', $params);
    }

    public function actionFlush()
    {
        $this->forcePostRequest();
        Log::deleteAll();

		return $this->redirect(['index']);
    }

}
