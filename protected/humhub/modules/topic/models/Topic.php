<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\models;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\content\services\ContentTagService;
use humhub\modules\space\models\Space;
use humhub\modules\stream\helpers\StreamHelper;
use humhub\modules\topic\permissions\AddTopic;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;

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

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('TopicModule.base', 'Name'),
            'color' => Yii::t('TopicModule.base', 'Color'),
            'sort_order' => Yii::t('TopicModule.base', 'Sort order'),
        ];
    }

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
    public function getUrl(?ContentContainerActiveRecord $contentContainer = null)
    {
        return StreamHelper::createUrl($contentContainer ?: $this->container, ['topicId' => $this->id]);
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
            if (is_string($topic) && strpos($topic, '_add:') === 0 && $canAdd) {
                $newTopic = new Topic([
                    'name' => substr($topic, strlen('_add:')),
                    'contentcontainer_id' => $content->contentcontainer_id,
                ]);

                if ($newTopic->save()) {
                    $result[] = $newTopic;
                }

            } elseif (is_numeric($topic)) {
                $topic = Topic::findOne((int)$topic);
                if ($topic) {
                    $result[] = $topic;
                }
            } elseif ($topic instanceof Topic) {
                $result[] = $topic;
            }
        }

        (new ContentTagService($content))->addTags($result);
    }

    public static function convertToGlobal(?string $containerType = null, ?string $topicName = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $topicsQuery = static::find()
                ->addSelect('')
                ->where(['IS NOT', 'contentcontainer_id', null])
                ->andWhere(['module_id' => (new static())->moduleId])
                ->andFilterWhere(['name' => $topicName]);

            if ($containerType) {
                $topicsQuery->innerJoinWith(['contentContainer contentContainer' => function (ActiveQuery $query) use ($containerType) {
                    $query->andOnCondition(['contentContainer.class' => $containerType]);
                }], false);
            }

            foreach ($topicsQuery->all() as $topic) {
                $existingGlobalTopic = Topic::find()->where(['name' => $topic->name, 'contentcontainer_id' => null])->one();

                if ($existingGlobalTopic) {
                    $globalTopic = $existingGlobalTopic;
                } else {
                    $globalTopic = new static([
                        'name' => $topic->name,
                        'module_id' => $topic->module_id,
                        'type' => $topic->type,
                        'color' => $topic->color,
                        'sort_order' => $topic->sort_order,
                        'contentcontainer_id' => null,
                    ]);
                    $globalTopic->save(false);
                }
                ContentTagRelation::updateAll(['tag_id' => $globalTopic->id], ['tag_id' => $topic->id]);

                $topic->delete();
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function isAllowedToCreate(ContentContainerActiveRecord $contentContainer)
    {
        return (
            $contentContainer instanceof Space
            && Yii::$app->getModule('space')->settings->get('allowSpaceTopics', true)
        )
        || (
            $contentContainer instanceof User
            && Yii::$app->getModule('user')->settings->get('auth.allowUserTopics', true)
        );
    }
}
