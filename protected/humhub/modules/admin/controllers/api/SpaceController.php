<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\admin\components\SpaceTable;
use Yii;

/**
 * The rows of the administration's space table (Administration › Spaces, see
 * `docs/api/src/admin.yaml`): `GET admin/space` answers the {@see SpaceTable} rows of the spaces
 * of `AdminSpaceList` (its filters and sorts as query parameters) in the paginated envelope.
 *
 * For those who may see the table ({@see SpaceTable::canAccess()}).
 *
 * @since 1.20
 */
class SpaceController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(SpaceTable::class);
    }
}
