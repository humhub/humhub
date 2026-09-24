<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\api\ApiRules;
use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\menu\DropdownDivider;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\DropdownMenu;
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
    public $id = 'space-header-controls-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->label) {
            $this->icon = 'controls';
        }

        if ($this->template === '@humhub/widgets/views/dropdownNavigation') {
            $this->template = '@humhub/widgets/menu/views/dropdown-menu.php';
        }


        // check user rights
        if ($this->space->isAdmin()) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.base', 'Settings'),
                'url' => $this->space->createUrl('/space/manage'),
                'icon' => 'cogs',
                'sortOrder' => 100,
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
                'sortOrder' => 300,
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

            if (!$membership->send_notifications && !Yii::$app->notification->hasSpace($this->space)) {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Receive Notifications for new content'),
                    'url' => $this->space->createUrl('/space/membership/receive-notifications'),
                    'icon' => 'bell',
                    'sortOrder' => 600,
                    'htmlOptions' => ['data-method' => 'POST'],
                ]));
            } else {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Don\'t receive notifications for new content'),
                    'url' => $this->space->createUrl('/space/membership/revoke-notifications'),
                    'icon' => 'bell-o',
                    'sortOrder' => 600,
                    'htmlOptions' => ['data-method' => 'POST'],
                ]));
            }

            if (!$this->space->isSpaceOwner() && $this->space->canLeave()) {
                // The same transition the membership button offers, through the same endpoint
                // (`DELETE /api/v2/space/<id>/membership`): the `space.leave` client action
                // calls it and reloads, see humhub.space.js.
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Cancel Membership'),
                    'url' => '#',
                    'icon' => 'remove',
                    'sortOrder' => 700,
                    'htmlOptions' => [
                        'data-action-click' => 'space.leave',
                        'data-action-url' => ApiRules::url('space/' . $this->space->id . '/membership'),
                        'data-action-confirm-header' => Yii::t('SpaceModule.base', '<strong>Leave</strong> Space'),
                        'data-action-confirm' => Yii::t('SpaceModule.base', 'Would you like to end your membership in Space {spaceName}?', ['{spaceName}' => '<strong>' . Html::encode($this->space->getDisplayName()) . '</strong>']),
                        'data-action-confirm-text' => Yii::t('SpaceModule.base', 'Leave'),
                    ],
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
                        'data-bs-toggle' => 'tooltip',
                        'data-bs-placement' => 'left',
                        'data-bs-title' => Yii::t('SpaceModule.manage', 'This option will hide new content from this space at your dashboard'),
                    ],
                ]));
            } else {
                $this->addEntry(new MenuLink([
                    'label' => Yii::t('SpaceModule.manage', 'Show posts on dashboard'),
                    'url' => $this->space->createUrl('/space/membership/switch-dashboard-display', ['show' => 1]),
                    'icon' => 'eye',
                    'sortOrder' => 800,
                    'htmlOptions' => ['data-method' => 'POST',
                        'data-bs-toggle' => 'tooltip',
                        'data-bs-placement' => 'left',
                        'data-bs-title' => Yii::t('SpaceModule.manage', 'This option will show new content from this space at your dashboard'),
                    ],
                ]));
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        if (!$this->label) {
            $options['aria-label'] = Yii::t('SpaceModule.base', 'Space actions');
        }

        return $options;
    }
}
