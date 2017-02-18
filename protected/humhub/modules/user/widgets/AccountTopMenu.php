<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use yii\helpers\Url;
use humhub\widgets\BaseMenu;

/**
 * AccountTopMenu Widget
 *
 * @author luke
 */
class AccountTopMenu extends BaseMenu
{

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
            return;
        }

        $user = Yii::$app->user->getIdentity();
        $this->addItem(array(
            'label' => Yii::t('base', 'My profile'),
            'icon' => '<i class="fa fa-user"></i>',
            'url' => $user->createUrl('/user/profile/home'),
            'sortOrder' => 100,
        ));
        $this->addItem(array(
            'label' => Yii::t('base', 'Account settings'),
            'icon' => '<i class="fa fa-edit"></i>',
            'url' => Url::toRoute('/user/account/edit'),
            'sortOrder' => 200,
        ));

        if (\humhub\modules\admin\widgets\AdminMenu::canAccess()) {
            $this->addItem(array(
                'label' => '---',
                'url' => '#',
                'sortOrder' => 300,
            ));

            $this->addItem(array(
                'label' => Yii::t('base', 'Administration'),
                'icon' => '<i class="fa fa-cogs"></i>',
                'url' => Url::toRoute('/admin'),
                'sortOrder' => 400,
            ));
        }

        $this->addItem(array(
            'label' => '---',
            'url' => '#',
            'sortOrder' => 600,
        ));

        $this->addItem(array(
            'label' => Yii::t('base', 'Logout'),
            'id' => 'account-logout',
            'icon' => '<i class="fa fa-sign-out"></i>',
            'pjax' => false,
            'url' => Url::toRoute('/user/auth/logout'),
            'sortOrder' => 700,
        ));

        parent::init();
    }

}
