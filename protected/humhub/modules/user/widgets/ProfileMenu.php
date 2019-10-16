<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use humhub\modules\user\models\User;
use humhub\modules\user\permissions\ViewAboutPage;

/**
 * ProfileMenuWidget shows the (usually left) navigation on user profiles.
 *
 * Only a controller which uses the 'application.modules_core.user.ProfileControllerBehavior'
 * can use this widget.
 *
 * The current user can be gathered via:
 *  $user = Yii::$app->getController()->getUser();
 *
 * @since 0.5
 * @author Luke
 */
class ProfileMenu extends LeftNavigation
{

    /**
     * @var User
     */
    public $user;


    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->panelTitle = Yii::t('UserModule.profile', '<strong>Profile</strong> menu');

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.profile', 'Stream'),
            'icon' => 'bars',
            'url' => $this->user->createUrl('//user/profile/home'),
            'sortOrder' => 200,
            'isActive' =>  MenuLink::isActiveState('user', 'profile', ['index', 'home'])
        ]));


        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.profile', 'About'),
            'icon' => 'info-circle',
            'url' => $this->user->createUrl('/user/profile/about'),
            'sortOrder' => 300,
            'isActive' =>  MenuLink::isActiveState('user', 'profile', 'about'),
            'isVisible' => $this->user->permissionManager->can(ViewAboutPage::class)
        ]));

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest && $this->user->visibility != User::VISIBILITY_ALL) {
            return '';
        }

        return parent::run();
    }

}

?>
