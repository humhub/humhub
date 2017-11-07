<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\models;

use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\topic\permissions\AddTopic;
use humhub\modules\content\models\ContentTag;

class Topic extends ContentTag
{
    public $moduleId = 'topic';

    public static function getLabel()
    {
        return Yii::t('TopicModule.base', 'Topic');
    }

    public static function attach(Content $content, $topics)
    {
        /* @var $result static[] */
        $result = [];

        // Clear all relations and append them again
        static::deleteContentRelations($content);

        $canAdd = $content->container->can(AddTopic::class);

        foreach ($topics as $topic) {
            if(strpos($topic, '_add:') === 0 && $canAdd) {
                $newTopic = new Topic([
                    'name' => substr($topic, strlen('_add:')),
                    'contentcontainer_id' => $content->contentcontainer_id
                ]);

                if($newTopic->save()) {
                    $result[] = $newTopic;
                }

            } else if(is_numeric($topic)) {
                $topic = Topic::findOne((int) $topic);
                if($topic) {
                    $result[] = $topic;
                }
            }
        }

        $content->addTags($result);
    }
}