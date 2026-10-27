<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\widgets;

use humhub\components\table\DataTable;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\topic\components\TopicTable as TopicTableDefinition;
use humhub\widgets\DataTableWidget;
use Yii;

/**
 * The topics to manage as a `<data-table>` island ({@see TopicTableDefinition}) inside the
 * topic page's card: the global topics (`container` null), or a space's or a user's, with the
 * rows' endpoint `GET /api/v2/admin/topic`, `/space/<id>/topic` or `/user/<id>/topic`.
 *
 * @since 1.20
 */
class TopicTable extends DataTableWidget
{
    /**
     * @inheritdoc
     */
    public bool $embedded = true;

    /**
     * @var ContentContainerActiveRecord|null the space or user whose topics are listed; the
     * global topics without
     */
    public ?ContentContainerActiveRecord $container = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->tableId = $this->container === null ? 'admin-topics' : 'container-topics';
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(['class' => TopicTableDefinition::class, 'container' => $this->container]);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        if ($this->container === null) {
            return ['/topic/api/topic-table/index'];
        }

        return ['/topic/api/topic-table/index', ($this->container instanceof Space ? 'spaceId' : 'userId') => $this->container->id];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('TopicModule.base', 'Topics');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('TopicModule.base', '{count} topic'),
            'other' => Yii::t('TopicModule.base', '{count} topics'),
            'range' => Yii::t('TopicModule.base', '{from}–{to} of {total} topics'),
        ];
    }
}
