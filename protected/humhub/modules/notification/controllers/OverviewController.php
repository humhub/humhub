<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\notification\models\forms\FilterForm;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\widgets\OverviewWidget;
use Yii;
use yii\data\Pagination;
use yii\db\IntegrityException;

/**
 * ListController
 *
 * @package humhub.modules_core.notification.controllers
 * @since 0.5
 */
class OverviewController extends Controller
{
    const PAGINATION_PAGE_SIZE = 20;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ]
        ];
    }

    /**
     * Returns a List of all notifications for the session user
     */
    public function actionIndex()
    {
        $notifications = [];
        $filterForm = $this->initFilterForm('GET');
        $query = Notification::findGrouped();

        if ($filterForm->hasFilter()) {
            $query->andFilterWhere(['not in', 'class', $filterForm->getExcludeClassFilter()]);
        } else {
            return $this->render('index', [
                'filterForm' => $filterForm,
                'pagination' => null,
                'notifications' => $notifications
            ]);
        }

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => static::PAGINATION_PAGE_SIZE]);
        $query->offset($pagination->offset)->limit($pagination->limit);

        $this->prepareNotifications($query, $notifications);

        return $this->render('index', [
            'notifications' => $notifications,
            'filterForm' => $filterForm,
            'pagination' => $pagination
        ]);
    }

    public function actionReload()
    {
        $notifications = [];
        $filterForm = $this->initFilterForm('POST');

        $query = Notification::findGrouped();

        if ($filterForm->hasFilter()) {
            $query->andFilterWhere(['not in', 'class', $filterForm->getExcludeClassFilter()]);
        } else {
            return OverviewWidget::widget(['notifications' => $notifications, 'pagination' => null]);
        }

        $countQuery = clone $query;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => static::PAGINATION_PAGE_SIZE,
            'route' => '/notification/overview/index',
            'params' => Yii::$app->request->post()
        ]);
        $pagination->setPage(0);
        $query->offset($pagination->offset)->limit($pagination->limit);
        $this->prepareNotifications($query, $notifications);

        return OverviewWidget::widget(['notifications' => $notifications, 'pagination' => $pagination]);
    }

    private function initFilterForm($type)
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }

        $filterForm = new FilterForm();
        switch ($type) {
            case 'GET' :
                $filterForm->load(Yii::$app->request->get());
                break;
            case 'POST' :
                $filterForm->load(Yii::$app->request->post());
                break;
        }

        return $filterForm;
    }

    private function prepareNotifications($query, & $notifications)
    {
        foreach ($query->all() as $notificationRecord) {
            /* @var $notificationRecord \humhub\modules\notification\models\Notification */

            try {
                $baseModel = $notificationRecord->getBaseModel();

                if($baseModel->validate()) {
                    $notifications[] = $baseModel;
                } else {
                    throw new IntegrityException('Invalid base model found for notification');
                }

            } catch (IntegrityException $ex) {
                $notificationRecord->delete();
                Yii::warning('Deleted inconsistent notification with id ' . $notificationRecord->id . '. ' . $ex->getMessage());
            }
        }
    }
}
