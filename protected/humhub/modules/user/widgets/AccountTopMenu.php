<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use humhub\widgets\ModalButton;
use Yii;
use yii\helpers\Url;
use humhub\modules\admin\widgets\AdminMenu;

/**
 * AccountTopMenu Widget
 *
 * @author luke
 */
class AccountTopMenu extends Menu
{
    public $id = 'account-top-menu';

    /**
     * @var boolean show user name
     */
    public $showUserName = true;

    /**
     * @inheritdoc
     */
    public $template = "@humhub/modules/user/widgets/views/accountTopMenu";

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->user->isGuest) {

            $signUpText = Yii::$app->getModule('user')->settings->get('auth.anonymousRegistration')
                ? Yii::t('UserModule.base', 'Sign in / up')
                : Yii::t('UserModule.base', 'Sign in');


            $this->addEntry(new MenuLink([
                'link' => ModalButton::primary($signUpText)->load(Url::toRoute('/user/auth/login'))->cssClass('btn-enter'),
                'sortOrder' => 100
            ]));

            parent::init();
            return;
        }

        $user = Yii::$app->user->getIdentity();

        $this->addEntry(new MenuLink([
            'label' => Yii::t('base', 'My profile'),
            'icon' => 'user',
            'url' => $user->createUrl('/user/profile/home'),
            'sortOrder' => 100]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('base', 'Account settings'),
            'icon' => 'edit',
            'url' => Url::toRoute('/user/account/edit'),
            'sortOrder' => 200,
        ]));

        if (AdminMenu::canAccess()) {
            $this->addEntry(new DropdownDivider(['sortOrder' => 300]));


            $this->addEntry(new MenuLink([
                'label' => Yii::t('base', 'Administration'),
                'icon' => 'cogs',
                'url' => Url::toRoute('/admin'),
                'sortOrder' => 400,
            ]));
        }

        $this->addEntry(new DropdownDivider(['sortOrder' => 600]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('base', 'Logout'),
            'id' => 'account-logout',
            'icon' => 'sign-out',
            'pjaxEnabled' => false,
            'url' => Url::toRoute('/user/auth/logout'),
            'sortOrder' => 700,
        ]));

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav'
        ];
    }

}
