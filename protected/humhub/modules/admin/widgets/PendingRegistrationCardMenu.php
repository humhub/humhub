<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The ⋮ menu of the pending registrations' table: the exports.
 *
 * @since 1.20
 */
class PendingRegistrationCardMenu extends DropdownMenu
{
    /**
     * @inheritdoc
     */
    public $id = 'admin-pending-registration-card-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'id' => 'export-csv',
            'label' => Yii::t('AdminModule.user', 'Export as CSV'),
            'url' => ['/admin/pending-registrations/export', 'format' => 'csv'],
            'icon' => 'file-text',
            'sortOrder' => 100,
        ]));
        $this->addEntry(new MenuLink([
            'id' => 'export-xlsx',
            'label' => Yii::t('AdminModule.user', 'Export as Excel'),
            'url' => ['/admin/pending-registrations/export', 'format' => 'xlsx'],
            'icon' => 'file-spreadsheet',
            'sortOrder' => 200,
        ]));

        parent::init();
    }
}
