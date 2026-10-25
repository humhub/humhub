<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The header actions of the administration's group list: "Create new group".
 *
 * @since 1.20
 */
class AdminGroupCardActions extends CardHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'admin-group-card-actions';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->user->can(ManageGroups::class)) {
            $this->addEntry(new MenuLink([
                'id' => 'add-group',
                'label' => Yii::t('AdminModule.user', 'Create new group'),
                'url' => ['/admin/group/edit'],
                'icon' => 'plus',
                'isPrimary' => true,
                'sortOrder' => 100,
            ]));
        }

        parent::init();
    }
}
