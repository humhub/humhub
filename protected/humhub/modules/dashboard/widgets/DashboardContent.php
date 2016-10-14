<?php

namespace humhub\modules\dashboard\widgets;

use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;

class DashboardContent extends Widget
{
    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @var boolean
     */
    public $showProfilePostForm = false;

    public function run()
    {
        if ($this->showProfilePostForm) {
            echo \humhub\modules\post\widgets\Form::widget(['contentContainer' => $this->contentContainer]);
        }

        if ($this->contentContainer === null) {
            $messageStreamEmpty = Yii::t('DashboardModule.views_dashboard_index_guest', '<b>No public contents to display found!</b>');
        } else {
            $messageStreamEmpty = Yii::t('DashboardModule.views_dashboard_index', '<b>Your dashboard is empty!</b><br>Post something on your profile or join some spaces!');
        }

        echo \humhub\modules\content\widgets\Stream::widget([
            'streamAction' => '//dashboard/dashboard/stream',
            'showFilters' => false,
            'messageStreamEmpty' => $messageStreamEmpty
        ]);
    }
}
