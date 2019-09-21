<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use Yii;

/**
 * The Admin Navigation for spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class HeaderControlsMenu extends DropdownMenu
{
    /**
     * @var Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public $label = '<i class="fa fa-cog"></i>';

    /**
     * @inheritdoc
     */
    public $id = 'space-header-controls-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {

        if ($this->template === '@humhub/widgets/views/dropdownNavigation') {
            $this->template = '@ui/menu/widgets/views/dropdown-menu.php';
        }


        // check user rights
        if ($this->space->isAdmin()) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.base', 'Settings'),
                'url' => $this->space->createUrl('/space/manage'),
                'icon' => 'cogs',
                'sortOrder' => 100
            ]));

            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.manage', 'Security'),
                'url' => $this->space->createUrl('/space/manage/security'),
                'icon' => 'lock',
                'sortOrder' => 200,
            ]));

            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.manage', 'Members'),
                'url' => $this->space->createUrl('/space/manage/member'),
                'icon' => 'group',
                'sortOrder' => 300
            ]));

            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.manage', 'Modules'),
                'url' => $this->space->createUrl('/space/manage/module'),
                'icon' => 'rocket',
                'sortOrder' => 400,
            ]));

            $this->addEntry(new DropdownDivider(['sortOrder' => 500]));
        }

        if ($this->space->isMember()) {
            $membership = $this->space->getMembership();

            if (!$membership->send_notifications) {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Receive Notifications for new content'),
                    'url' => $this->space->createUrl('/space/membership/receive-notifications'),
                    'icon' => 'bell',
                    'sortOrder' => 600,
                    'htmlOptions' => ['data-method' => 'POST']
                ]));
            } else {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Don\'t receive notifications for new content'),
                    'url' => $this->space->createUrl('/space/membership/revoke-notifications'),
                    'icon' => 'bell-o',
                    'sortOrder' => 600,
                    'htmlOptions' => ['data-method' => 'POST']
                ]));
            }

            if (!$this->space->isSpaceOwner() && $this->space->canLeave()) {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Cancel Membership'),
                    'url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'icon' => 'times',
                    'sortOrder' => 700,
                    'htmlOptions' => ['data-method' => 'POST']
                ]));
            }

            if ($membership->show_at_dashboard) {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Hide posts on dashboard'),
                    'url' => $this->space->createUrl('/space/membership/switch-dashboard-display', ['show' => 0]),
                    'icon' => 'eye-slash',
                    'sortOrder' => 800,
                    'htmlOptions' => [
                        'data-method' => 'POST',
                        'class' => 'tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'title' => Yii::t('SpaceModule.manage', 'This option will hide new content from this space at your dashboard')
                    ]
                ]));
            } else {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Show posts on dashboard'),
                    'url' => $this->space->createUrl('/space/membership/switch-dashboard-display', ['show' => 1]),
                    'icon' => 'fa-eye',
                    'sortOrder' => 800,
                    'htmlOptions' => ['data-method' => 'POST',
                        'class' => 'tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'title' => Yii::t('SpaceModule.manage', 'This option will show new content from this space at your dashboard')
                    ]
                ]));
            }
        }

        return parent::init();
    }
}
