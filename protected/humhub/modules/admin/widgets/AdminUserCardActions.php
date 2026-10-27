<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The header actions of the administration's user table ({@see UserTable}): "Add new user".
 *
 * @since 1.20
 */
class AdminUserCardActions extends CardHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'admin-user-card-actions';

    /**
     * @inheritdoc
     */
    public function init()
    {
        // As the user administration itself (`UserController::actionAdd()`)
        if (Yii::$app->user->can([ManageUsers::class, ManageGroups::class])) {
            $this->addEntry(new MenuLink([
                'id' => 'add-user',
                'label' => Yii::t('AdminModule.user', 'Add new user'),
                'url' => ['/admin/user/add'],
                'icon' => 'user-plus',
                'isPrimary' => true,
                'sortOrder' => 100,
            ]));
        }

        parent::init();
    }
}
