<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\content\widgets\ContentTagPicker;
use humhub\modules\topic\assets\TopicVueAsset;
use humhub\modules\topic\models\Topic;
use Yii;
use yii\helpers\Url;

/**
 * This InputWidget class can be used to add a topic picker input field. The topic picker field is only
 * rendered if there are topics available or if the user is allowed to create topics.
 */
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

    /**
     * Whether $url was passed in (a legacy search endpoint) rather than filled by init().
     */
    private bool $ownUrl = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->ownUrl = $this->url !== null;

        if (!$this->contentContainer) {
            $this->contentContainer = ContentContainerHelper::getCurrent();
        }

        if (!$this->url) {
            $this->url = $this->contentContainer
                ? $this->contentContainer->createUrl('/topic/topic/search')
                : Url::to(['/topic/topic/search']);
        }

        $this->addOptions = Topic::isAllowedToCreate($this->contentContainer);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!static::showTopicPicker($this->contentContainer)) {
            return $this->emptyResult();
        }

        // The `topic` picker source
        TopicVueAsset::register($this->getView());

        return parent::run();
    }

    /**
     * Determines if a topicpicker should be rendered for the current user. This is only the case if there are topics
     * available for the given container or the user is allowed to create topics.
     *
     * @param ContentContainerActiveRecord|null $container
     * @return bool
     */
    public static function showTopicPicker(?ContentContainerActiveRecord $container = null)
    {
        return Topic::isAllowedToCreate($container) || static::hasTopics($container);
    }

    /**
     * Determines if the current user is allowed to add topics on this container.
     *
     * @return bool
     * @since 1.6
     * @deprecated since 1.18.2 use Topic::isAllowedToCreate() instead
     */
    private static function canAddTopic(?ContentContainerActiveRecord $container = null)
    {
        return Topic::isAllowedToCreate($container);
    }

    /**
     * Checks if there are topics available on this container.
     *
     * @return bool
     */
    private static function hasTopics(?ContentContainerActiveRecord $container = null)
    {
        return (($container ? Topic::findByContainer($container)->count() : 0) + Topic::find()->count()) > 0;
    }

    /**
     * @inheritdoc
     * @param $tags
     * @return array
     */
    public static function jsonResult($tags)
    {
        $result = parent::jsonResult($tags);
        foreach ($result as $key => $tag) {
            $result[$key]['image'] = $result[$key]['image'] ?: Yii::$app->getModule('topic')->icon;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getItemImage($item)
    {
        return $item->color ?: Yii::$app->getModule('topic')->icon;
    }

    /**
     * @inheritdoc
     * @since 1.20
     */
    public function getSource(): ?array
    {
        if ($this->ownUrl) {
            return null;
        }

        return ['source' => 'topic', 'params' => array_filter([
            'containerId' => $this->contentContainer?->contentcontainer_id,
            'icon' => Yii::$app->getModule('topic')->icon,
        ])];
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        $result = parent::getData();
        $allowMultiple = $this->maxSelection !== 1;
        $result['placeholder'] = Yii::t('TopicModule.widgets_TopicPicker', 'Select {n,plural,=1{topic} other{topics}}', ['n' => ($allowMultiple) ? 2 : 1]);
        $result['placeholder-more'] = Yii::t('TopicModule.widgets_TopicPicker', 'Select topic...');
        $result['no-result'] = Yii::t('TopicModule.widgets_TopicPicker', 'No topics found for the given query');

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('TopicModule.widgets_TopicPicker', 'This field only allows a maximum of {n,plural,=1{# topic} other{# topics}}', ['n' => $this->maxSelection]);
        }

        return $result;
    }
}
