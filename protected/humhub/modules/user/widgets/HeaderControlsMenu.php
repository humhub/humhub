<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\user\serializers\FollowSerializer;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\WidgetMenuEntry;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * The header controls menu for user
 *
 * @author Luke
 * @package humhub.modules_core.user.widgets
 * @since 1.16
 */
class HeaderControlsMenu extends DropdownMenu
{
    public ?User $user = null;

    /**
     * @inheritdoc
     */
    public $id = 'user-header-controls-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->icon = 'controls';

        $this->initEditControl();
        $this->initBlockControl();
        $this->initFollowControl();
    }

    protected function initEditControl(): void
    {
        if (Yii::$app->user->isGuest || !Yii::$app->user->can(ManageUsers::class)) {
            return;
        }

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Edit'),
            'url' => Url::to(['/admin/user/edit', 'id' => $this->user->id]),
            'icon' => 'pencil',
            'sortOrder' => 100,
        ]));
    }

    protected function initBlockControl(): void
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        if (!$this->user instanceof User || $this->user->isCurrentUser()) {
            return;
        }

        if (!$this->user->allowBlockUsers()) {
            return;
        }

        if (Yii::$app->user->identity->isBlockedForUser($this->user)) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('UserModule.base', 'Unblock user'),
                'url' => $this->user->createUrl('/user/profile/unblock'),
                'icon' => 'check',
                'htmlOptions' => ['data-method' => 'post'],
                'sortOrder' => 200,
            ]));
        } else {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('UserModule.base', 'Block user'),
                'url' => $this->user->createUrl('/user/profile/block'),
                'icon' => 'ban',
                'htmlOptions' => ['data-method' => 'post'],
                'sortOrder' => 200,
            ]));
        }
    }

    /**
     * While the friendship system is on, the profile header shows the `FriendshipButton` and
     * following moves into this menu: the entry is the `UserFollowButton` island (as a
     * `dropdown-item`), which follows and unfollows through `/api/v2/user/<id>/follow`, updates
     * itself and keeps the page's other follow and friendship buttons of the user in sync
     * (`user:follow-changed`).
     */
    protected function initFollowControl(): void
    {
        if (!FriendshipButton::isVisibleForUser($this->user)) {
            return;
        }

        if (Yii::$app->user->isGuest || $this->user->isCurrentUser()) {
            return;
        }

        // What the button offers: following, or ending a follow after following was disabled.
        if (!FollowSerializer::canFollow($this->user) && !$this->user->isFollowedByUser()) {
            return;
        }

        $this->addEntry(new WidgetMenuEntry([
            'id' => 'follow',
            'widgetClass' => UserFollowButton::class,
            'widgetOptions' => [
                'user' => $this->user,
                'followOptions' => ['class' => 'dropdown-item'],
                'unfollowOptions' => ['class' => 'dropdown-item'],
                'followIcon' => 'paper-plane',
            ],
            'sortOrder' => 300,
        ]));
    }

    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        if (!$this->label) {
            $options['aria-label'] = Yii::t('UserModule.base', 'User actions');
        }

        return $options;
    }
}
