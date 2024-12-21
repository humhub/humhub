<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\space\models\Space;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\modules\user\models\User;
use Yii;
use yii\bootstrap\Html;
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

        $this->label = Icon::get('controls');

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

    protected function initFollowControl(): void
    {
        if (!FriendshipButton::isVisibleForUser($this->user)) {
            return;
        }

        if (Yii::$app->user->isGuest || $this->user->isCurrentUser()) {
            return;
        }

        if ($this->user->isFollowedByUser()) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('UserModule.base', 'Following'),
                'url' => $this->user->createUrl('/user/profile/unfollow', ['redirect' => true]),
                'icon' => 'check',
                'htmlOptions' => [
                    'data-method' => 'post',
                    'data-action-confirm' => Yii::t('SpaceModule.base', 'Would you like to unfollow {userName}?', [
                        '{userName}' => '<strong>' . Html::encode($this->user->getDisplayName()) . '</strong>',
                    ]),
                ],
                'sortOrder' => 300,
            ]));
        } else {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('UserModule.base', 'Follow'),
                'url' => $this->user->createUrl('/user/profile/follow'),
                'icon' => 'paper-plane',
                'htmlOptions' => ['data-method' => 'post'],
                'sortOrder' => 300,
            ]));
        }
    }
}
