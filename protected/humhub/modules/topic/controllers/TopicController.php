<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\modules\topic\widgets\TopicSidebar;
use Yii;

class TopicController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public $requireContainer = false;

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['json'],
        ];
    }

    public function actionSearch($keyword)
    {
        return TopicPicker::searchByContainer($keyword, $this->contentContainer);
    }

    public function actionSidebarShowMore()
    {
        /* @var TopicSidebar $sidebar */
        $sidebar = Yii::createObject([
            'class' => TopicSidebar::class,
            'contentContainer' => $this->contentContainer,
            'mode' => TopicSidebar::MODE_MORE,
        ]);

        return $this->asJson($sidebar->getMoreTopicsData());
    }
}
