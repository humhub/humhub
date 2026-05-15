<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\widgets;

use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use Yii;
use yii\db\Expression;

class TopicSidebar extends Widget
{
    public ?ContentContainerActiveRecord $contentContainer = null;
    public int $limit = 20;

    /**
     * @var Topic[]
     */
    private ?array $_topics = null;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun() && $this->isVisible() && $this->hasTopics();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('topic-sidebar', [
            'topics' => $this->getTopics(),
        ]);
    }

    protected function isGlobal(): bool
    {
        return $this->contentContainer === null;
    }

    protected function isVisible(): bool
    {
        if ($this->isGlobal()) {
            return (bool) Yii::$app->getModule('dashboard')->settings->get('showTopicSidebar');
        }

        if ($this->contentContainer instanceof Space) {
            return $this->contentContainer->getAdvancedSettings()->showTopicSidebar;
        }

        return false;
    }

    protected function hasTopics(): bool
    {
        return $this->getTopics() !== [];
    }

    /**
     * @return Topic[]
     */
    protected function getTopics(): array
    {
        if ($this->_topics === null) {
            $query = Topic::findByContainer($this->contentContainer, true)
                ->limit($this->limit);

            // Sort topics by usage count in the container or globally
            $query->leftJoin('content_tag_relation', 'content_tag_relation.tag_id = content_tag.id')
                ->groupBy('content_tag.id')
                ->orderBy([
                    'usages_count' => SORT_DESC,
                    'name' => SORT_ASC,
                ]);
            if ($this->isGlobal()) {
                $query->addSelect(['usages_count' => new Expression('COUNT(content_tag_relation.id)')]);
            } else {
                $query->addSelect(['usages_count' => new Expression('COUNT(content.id)')])
                    ->leftJoin('content', [
                        'and',
                        'content.id = content_tag_relation.content_id',
                        ['content.contentcontainer_id' => $this->contentContainer->contentcontainer_id],
                    ]);
            }

            $this->_topics = $query->all();
        }

        return $this->_topics;
    }
}