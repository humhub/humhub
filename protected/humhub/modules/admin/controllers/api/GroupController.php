<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\admin\components\GroupTable;
use Yii;

/**
 * The rows of the administration's group table (Administration › Users › Groups, see
 * `docs/api/src/admin.yaml`): `GET admin/group` answers the {@see GroupTable} rows of the groups
 * of `AdminGroupList` (its filters and sorts as query parameters) in the paginated envelope.
 *
 * For those who may see the table ({@see GroupTable::canAccess()}).
 *
 * @since 1.20
 */
class GroupController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(GroupTable::class);
    }
}
