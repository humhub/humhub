<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\custom_pages\helpers\Html;
use humhub\modules\topic\widgets\TopicBadge;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\modules\topic\widgets\TopicSidebar;
use humhub\widgets\bootstrap\Button;
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

        $topics = '';
        $button = '';
        if ($sidebar->canShowMore()) {
            foreach ($sidebar->getTopics() as $topic) {
                $topics .= TopicBadge::forTopic($topic) . ' ';
            }
            $topics = Html::tag('span', $topics, ['class' => 'topic-sidebar-more-topics']);
            $button = Button::light(Yii::t('TopicModule.base', 'Show less'))
                ->action('showLess')
                ->cssClass('w-100 mt-3')
                ->sm()
                ->loader(false)
                ->asString();
        }

        return $this->asJson([
            'topics' => $topics,
            'button' => $button,
        ]);
    }
}
