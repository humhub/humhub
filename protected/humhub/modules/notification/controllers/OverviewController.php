<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\notification\models\forms\FilterForm;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\widgets\OverviewWidget;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
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
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ];
    }

    /**
     * @param bool $reload if the request is a reload request
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionIndex($reload = false)
    {
        $filterForm = $this->loadFilterForm($reload);

        if ($filterForm->hasFilter()) {
            $query = $filterForm->createQuery();
            $notifications = $this->prepareNotifications($query->all());
            $overview = OverviewWidget::widget([
                'notifications' => $notifications,
                'pagination' => $this->preparePagination($query)
            ]);
        } else {
            $overview = OverviewWidget::widget([
                'notifications' => [],
            ]);
        }

        return $reload ? $overview : $this->render('index', [
            'overview' => $overview,
            'filterForm' => $filterForm,
        ]);
    }

    /**
     * Creates a pagination instance from the given $query
     *
     * @param $query ActiveQuery
     * @return Pagination
     */
    private function preparePagination($query)
    {
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => static::PAGINATION_PAGE_SIZE]);
        $query->offset($pagination->offset)->limit($pagination->limit);
        return $pagination;

    }

    /**
     * Loads the filters from the request into the form
     *
     * @param bool $reload
     * @return FilterForm
     */
    private function loadFilterForm($reload = false)
    {
        $filterForm = new FilterForm();

        if ($reload) {
            $filterForm->load(Yii::$app->request->post());
        } else {
            $filterForm->load(Yii::$app->request->get());
        }

        return $filterForm;
    }

    /**
     * Validates given notifications and returns a list of notification models of all valid notifications.
     *
     * @param $notifications Notification[]
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function prepareNotifications($notifications)
    {
        $result = [];
        foreach ($notifications as $notificationRecord) {
            /* @var $notificationRecord \humhub\modules\notification\models\Notification */

            try {
                $baseModel = $notificationRecord->getBaseModel();

                if ($baseModel->validate()) {
                    $result[] = $baseModel;
                } else {
                    throw new IntegrityException('Invalid base model (' . $notificationRecord->class . ') found for notification');
                }

            } catch (IntegrityException $ex) {
                $notificationRecord->delete();
                Yii::warning('Deleted inconsistent notification with id ' . $notificationRecord->id . '. ' . $ex->getMessage());
            }
        }
        return $result;
    }
}
