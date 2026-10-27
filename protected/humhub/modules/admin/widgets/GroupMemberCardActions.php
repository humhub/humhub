<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\user\models\Group;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\helpers\Url;

/**
 * The header actions of a group's member table: "Add members" (a dialog), for those who may add
 * members.
 *
 * @since 1.20
 */
class GroupMemberCardActions extends CardHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'admin-group-member-card-actions';

    public Group $group;

    public bool $canAdd = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->canAdd) {
            $this->addEntry(new MenuLink([
                'id' => 'add-members',
                'label' => Yii::t('AdminModule.user', 'Add members'),
                'url' => '#',
                'icon' => 'user-plus',
                'isPrimary' => true,
                'sortOrder' => 100,
                'htmlOptions' => [
                    'data-action-click' => 'ui.modal.load',
                    'data-action-url' => Url::to(['/admin/group/add-members', 'id' => $this->group->id]),
                ],
            ]));
        }

        parent::init();
    }
}
