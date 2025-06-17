<?php

namespace humhub\modules\dashboard\widgets;

use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\post\widgets\Form;
use humhub\modules\stream\widgets\StreamViewer;
use Yii;

class DashboardContent extends Widget
{
    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @var bool
     */
    public $showProfilePostForm = false;

    public function run()
    {
        if ($this->showProfilePostForm) {
            echo Form::widget(['contentContainer' => $this->contentContainer]);
        }

        if ($this->contentContainer === null) {
            $messageStreamEmpty = Yii::t('DashboardModule.base', '<b>No public contents to display found!</b>');
        } else {
            $messageStreamEmpty = Yii::t('DashboardModule.base', '<b>Your dashboard is empty!</b><br>Post something on your profile or join some spaces!');
        }

        echo StreamViewer::widget([
            'options' => ['class' => 'dashboard-wall-stream'],
            'streamAction' => '//dashboard/dashboard/stream',
            'showFilters' => (bool)Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm'),
            'messageStreamEmpty' => $messageStreamEmpty,
        ]);
    }
}
