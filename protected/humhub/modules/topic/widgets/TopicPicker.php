<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;


use humhub\modules\topic\permissions\AddTopic;
use Yii;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\widgets\ContentTagPicker;
use humhub\modules\topic\models\Topic;

class TopicPicker extends ContentTagPicker
{
    /**
     * @inheritdoc
     */
    public $itemClass = Topic::class;

    /**
     * @inheritdoc
     */
    public $minInput = 2;

    /**
     * @inheritdoc
     */
    public $showDefaults = true;

    public function init()
    {
        if(!$this->contentContainer) {
            $controller = Yii::$app->controller;
            if($controller instanceof ContentContainerController) {
                $this->contentContainer = $controller->contentContainer;
            }
        }


        if(!$this->url && $this->contentContainer) {
            $this->url = $this->contentContainer->createUrl('/topic/topic/search');
        }

        $this->addOptions = $this->contentContainer->can(AddTopic::class);

        parent::init();
    }

    protected function getData()
    {
        $result = parent::getData();
        $allowMultiple = $this->maxSelection !== 1;
        $result['placeholder'] = Yii::t('TopicModule.widgets_TopicPicker', 'Select {n,plural,=1{topic} other{topics}}', ['n' => ($allowMultiple) ? 2 : 1]);
        $result['placeholder-more'] = Yii::t('TopicModule.widgets_TopicPicker', 'Add topic');
        $result['no-result'] = Yii::t('TopicModule.widgets_TopicPicker', 'No topics found for the given query');

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('TopicModule.widgets_TopicPicker', 'This field only allows a maximum of {n,plural,=1{# topic} other{# topics}}', ['n' => $this->maxSelection]);
        }
        return $result;
    }
}