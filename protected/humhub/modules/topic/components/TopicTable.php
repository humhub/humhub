<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\components;

use humhub\components\listing\FilterableList;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\permissions\ManageTopics;
use humhub\modules\user\models\User;
use Yii;

/**
 * The topics to manage (Administration › Settings › Topics for the global ones, a space's or a
 * user's topic settings for theirs; `topic\widgets\TopicTable`, rows from
 * `GET /api/v2/admin/topic`, `/space/<id>/topic` or `/user/<id>/topic`): name, sort order and
 * the number of contents with the topic, with the actions edit (a dialog), view (the stream
 * filtered by it) and delete.
 *
 * Created through the DI container with the container (none for the global topics), so a module
 * may replace it or change columns and actions on {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class TopicTable extends DataTable
{
    /**
     * @var ContentContainerActiveRecord|null the space or user whose topics are listed; the
     * global topics without
     */
    public ?ContentContainerActiveRecord $container = null;

    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new TopicTableList(['container' => $this->container]);
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('TopicModule.base', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('contents', ColumnDefinition::TYPE_NUMBER, Yii::t('TopicModule.base', 'Contents'), priority: 2, sort: 'contents', sortOrder: 200, narrow: true),
            new ColumnDefinition('sortOrder', ColumnDefinition::TYPE_NUMBER, Yii::t('TopicModule.base', 'Sort order'), priority: 1, sort: 'sortOrder', sortOrder: 300, narrow: true),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        $container = $this->container;
        $route = static fn(string $global, string $own) => static fn(Topic $topic) => $container === null
            ? [$global, 'id' => $topic->id]
            : $container->createUrl($own, ['id' => $topic->id]);

        return [
            new RowAction(
                'edit',
                Yii::t('base', 'Edit'),
                RowAction::KIND_MODAL,
                $route('/admin/setting/edit-topic', '/topic/manage/edit'),
                icon: 'pencil',
                sortOrder: 100,
            ),
            new RowAction(
                'view',
                Yii::t('TopicModule.base', 'Show contents'),
                RowAction::KIND_LINK,
                static fn(Topic $topic) => $topic->getUrl($container),
                icon: 'filter',
                sortOrder: 200,
            ),
            new RowAction(
                'delete',
                Yii::t('base', 'Delete'),
                RowAction::KIND_REQUEST,
                $route('/admin/setting/delete-topic', '/topic/manage/delete'),
                icon: 'trash',
                confirm: Yii::t('TopicModule.base', 'Do you really want to delete this topic?'),
                destructive: true,
                sortOrder: 100,
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Topic $model */
        return [
            'name' => [
                'text' => $model->name,
                'url' => $model->getUrl($this->container),
            ],
            'contents' => ['text' => (string)ContentTagRelation::find()->where(['tag_id' => $model->id])->count()],
            'sortOrder' => ['text' => (string)(int)$model->sort_order],
        ];
    }

    /**
     * Whether the viewer may manage the topics: the global ones who may manage the settings, a
     * space's who may manage its topics, a user's the user and those who may manage all content
     * (as the topic pages).
     */
    public function canAccess(): bool
    {
        $container = $this->container;
        if ($container === null) {
            return Yii::$app->user->can(ManageSettings::class);
        }

        if (!Topic::isAllowedToCreate($container)) {
            return false;
        }

        if ($container instanceof Space) {
            return $container->can(ManageTopics::class);
        }

        return $container instanceof User
            && ($container->is(Yii::$app->user->getIdentity()) || (bool)Yii::$app->user->getIdentity()?->canManageAllContent());
    }
}
