<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\Invite;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The ⋮ menu of the administration's user table ({@see UserTable}): the exports and, while
 * there are any, the pending registrations.
 *
 * @since 1.20
 */
class AdminUserCardMenu extends DropdownMenu
{
    /**
     * @inheritdoc
     */
    public $id = 'admin-user-card-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'id' => 'export-csv',
            'label' => Yii::t('AdminModule.user', 'Export as CSV'),
            'url' => ['/admin/user/export', 'format' => 'csv'],
            'icon' => 'file-text',
            'sortOrder' => 100,
        ]));
        $this->addEntry(new MenuLink([
            'id' => 'export-xlsx',
            'label' => Yii::t('AdminModule.user', 'Export as Excel'),
            'url' => ['/admin/user/export', 'format' => 'xlsx'],
            'icon' => 'file-spreadsheet',
            'sortOrder' => 200,
        ]));

        if (Yii::$app->user->can([ManageUsers::class, ManageGroups::class])
            && Invite::find()->where(Invite::filterSource())->exists()) {
            $this->addEntry(new MenuLink([
                'id' => 'pending-registrations',
                'label' => Yii::t('AdminModule.user', 'List pending registrations'),
                'url' => ['/admin/pending-registrations'],
                'icon' => 'user-question',
                'sortOrder' => 300,
            ]));
        }

        parent::init();
    }
}
