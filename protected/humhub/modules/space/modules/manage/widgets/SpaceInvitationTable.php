<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use humhub\components\table\DataTable;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\components\SpaceInvitationTable as SpaceInvitationTableDefinition;
use humhub\widgets\DataTableWidget;
use Yii;

/**
 * The open invitations of a space (Space › Settings › Members › Pending invites) as a
 * `<data-table>` island ({@see SpaceInvitationTableDefinition}), the card below the members' page header, with
 * the rows' endpoint, `GET /api/v2/space/<id>/invitation`.
 *
 * @since 1.20
 */
class SpaceInvitationTable extends DataTableWidget
{
    public const TABLE_ID = 'space-invitations';

    /**
     * @inheritdoc
     */
    public string $tableId = self::TABLE_ID;

    /**
     * @inheritdoc
     */
    public bool $selectByDefault = true;

    public Space $space;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = Yii::t('SpaceModule.manage', 'Pending Invites');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(['class' => SpaceInvitationTableDefinition::class, 'space' => $this->space]);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/space/api/invitation/index', 'id' => $this->space->id];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('SpaceModule.manage', 'Open invitations of {space}', ['space' => $this->space->name]);
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('SpaceModule.manage', '{count} invitation'),
            'other' => Yii::t('SpaceModule.manage', '{count} invitations'),
            'range' => Yii::t('SpaceModule.manage', '{from}–{to} of {total} invitations'),
        ];
    }
}
