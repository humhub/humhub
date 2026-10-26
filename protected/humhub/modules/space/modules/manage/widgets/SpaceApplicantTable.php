<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use humhub\components\table\DataTable;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\components\SpaceApplicantTable as SpaceApplicantTableDefinition;
use humhub\widgets\DataTableWidget;
use Yii;

/**
 * The membership requests of a space (Space › Settings › Members › Pending approvals) as a
 * `<data-table>` island ({@see SpaceApplicantTableDefinition}), the card below the members' page header, with
 * the rows' endpoint, `GET /api/v2/space/<id>/applicant`.
 *
 * @since 1.20
 */
class SpaceApplicantTable extends DataTableWidget
{
    public const TABLE_ID = 'space-applicants';

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
        $this->title = Yii::t('SpaceModule.manage', 'Pending Approvals');
    }

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(['class' => SpaceApplicantTableDefinition::class, 'space' => $this->space]);
    }

    /**
     * @inheritdoc
     */
    protected function getUrl(): string|array
    {
        return ['/space/api/applicant/index', 'id' => $this->space->id];
    }

    /**
     * @inheritdoc
     */
    protected function getLabel(): string
    {
        return Yii::t('SpaceModule.manage', 'Membership requests of {space}', ['space' => $this->space->name]);
    }

    /**
     * @inheritdoc
     */
    protected function getCountLabels(): array
    {
        return [
            'one' => Yii::t('SpaceModule.manage', '{count} request'),
            'other' => Yii::t('SpaceModule.manage', '{count} requests'),
            'range' => Yii::t('SpaceModule.manage', '{from}–{to} of {total} requests'),
        ];
    }
}
