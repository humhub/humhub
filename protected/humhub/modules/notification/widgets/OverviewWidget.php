<?php

namespace humhub\modules\notification\widgets;

use humhub\widgets\JsWidget;
use humhub\widgets\Reloadable;
use Yii;
use yii\helpers\Url;

class OverviewWidget extends JsWidget implements Reloadable
{
    public $id = 'notification_overview_list';

    public $init = true;
    
    public $notifications;

    public $pagination;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }
        return $this->render('notificationOverviewList', [
            'notifications' => $this->notifications,
            'pagination' => $this->pagination,
            'options' => $this->getOptions()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getReloadUrl()
    {
        return ['/notification/overview/index'];
    }
}