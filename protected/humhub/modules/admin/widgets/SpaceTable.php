<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\table\DataTable;
use humhub\modules\admin\components\SpaceTable as SpaceTableDefinition;
use humhub\widgets\DataTableWidget;
use humhub\widgets\menu\CardHeaderActions;
use Yii;

/**
 * The administration's space table (Administration › Spaces) as a `<data-table>` island
 * ({@see SpaceTableDefinition}), with "Add new space" ({@see AdminSpaceCardActions}) and the
 * rows' endpoint, `GET /api/v2/admin/space`.
 *
 * @since 1.20
 */
class SpaceTable extends DataTableWidget
{
    public const TABLE_ID = 'admin-spaces';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = Yii::t('AdminModule.space', 'Overview');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(SpaceTableDefinition::class);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/admin/api/space/index'];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('AdminModule.base', 'Spaces');
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('AdminModule.space', '{count} space'),
            'other' => Yii::t('AdminModule.space', '{count} spaces'),
            'range' => Yii::t('AdminModule.space', '{from}–{to} of {total} spaces'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderActions(): ?CardHeaderActions
    {
        return new AdminSpaceCardActions();
    }
}
