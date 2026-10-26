<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\oauth\components\table\AdminClientTable;
use Yii;

/**
 * The OAuth clients as rows of the administration's client table (`AdminClientTable`):
 * `GET admin/oauth-client` answers one page of them. Clients are edited and deleted on the
 * administration's pages, which the rows' actions name.
 *
 * For those who may manage the settings ({@see AdminClientTable::canAccess()}).
 *
 * @since 1.20
 */
class AdminClientController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(AdminClientTable::class);
    }
}
