<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use Yii;

/**
 * Member Header Controls Menu
 */
class MemberHeaderControlsMenu extends DropdownMenu
{
    /**
     * @var Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('SpaceModule.manage', 'Remove all members'),
            'url' => $this->space->createUrl('remove-all'),
            'sortOrder' => 100,
            'htmlOptions' => ['data-action-confirm' => Yii::t('SpaceModule.manage', 'All members excluding moderators and administrators of this Space will be removed. All pending invitations and membership requests will be terminated.')],
        ]));

        parent::init();
    }
}
