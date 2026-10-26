<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use humhub\components\table\DataTable;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\components\SpaceMemberTable as SpaceMemberTableDefinition;
use humhub\widgets\DataTableWidget;
use Yii;

/**
 * The members of a space (Space › Settings › Members) as a `<data-table>` island
 * ({@see SpaceMemberTableDefinition}), the card below the members' page header, with the rows' endpoint,
 * `GET /api/v2/space/<id>/member`.
 *
 * @since 1.20
 */
class SpaceMemberTable extends DataTableWidget
{
    public const TABLE_ID = 'space-members';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    public Space $space;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = Yii::t('SpaceModule.manage', 'Members');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(['class' => SpaceMemberTableDefinition::class, 'space' => $this->space]);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/space/api/member/index', 'id' => $this->space->id];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('SpaceModule.manage', 'Members of {space}', ['space' => $this->space->name]);
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('SpaceModule.manage', '{count} member'),
            'other' => Yii::t('SpaceModule.manage', '{count} members'),
            'range' => Yii::t('SpaceModule.manage', '{from}–{to} of {total} members'),
        ];
    }
}
