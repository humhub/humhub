<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\modules\user\models\User;
use Yii;

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

        $this->initBlockControl();
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
                'sortOrder' => 100
            ]));
        } else {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('UserModule.base', 'Block user'),
                'url' => $this->user->createUrl('/user/profile/block'),
                'icon' => 'ban',
                'sortOrder' => 100
            ]));
        }
    }
}
