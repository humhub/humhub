<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\models;

use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\stream\helpers\StreamHelper;
use humhub\modules\content\models\Content;
use humhub\modules\topic\permissions\AddTopic;
use humhub\modules\content\models\ContentTag;
use Yii;

/**
 * ContentTag type used for categorizing content.
 *
 * @since 1.3
 */
class Topic extends ContentTag
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'topic';

    /**
     * @inheritdoc
     */
    public static function getLabel()
    {
        return Yii::t('TopicModule.base', 'Topic');
    }

    /**
     * @return string topic icon used in badges etc.
     */
    public static function getIcon()
    {
        return Yii::$app->getModule('topic')->icon;
    }

    /**
     * @return string link to topic filter stream page
     */
    public function getUrl()
    {
        return StreamHelper::createUrl($this->container, ['topicId' => $this->id]);
    }

    /**
     * Attaches the given topics to the given content instance.
     *
     * @param Content $content target content
     * @param int[]|int|Topic|Topic[] $topics either a single or array of topics or topic Ids to add.
     */
    public static function attach(ContentOwner $contentOwner, $topics)
    {
        $content = $contentOwner->getContent();

        /* @var $result static[] */
        $result = [];

        // Clear all relations and append them again
        static::deleteContentRelations($content);

        $canAdd = $content->container->can(AddTopic::class);

        if (empty($topics)) {
            return;
        }

        $topics = is_array($topics) ? $topics : [$topics];

        foreach ($topics as $topic) {
            if(strpos($topic, '_add:') === 0 && $canAdd) {
                $newTopic = new Topic([
                    'name' => substr($topic, strlen('_add:')),
                    'contentcontainer_id' => $content->contentcontainer_id
                ]);

                if ($newTopic->save()) {
                    $result[] = $newTopic;
                }

            } elseif (is_numeric($topic)) {
                $topic = Topic::findOne((int) $topic);
                if ($topic) {
                    $result[] = $topic;
                }
            } elseif ($topic instanceof Topic) {
                $result[] = $topic;
            }
        }

        $content->addTags($result);
    }
}
