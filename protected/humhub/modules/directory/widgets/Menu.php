<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\directory\Module;
use humhub\modules\ui\menu\widgets\LeftNavigation;

/**
 * Directory module navigation
 *
 * @since 0.21
 * @author Luke
 */
class Menu extends LeftNavigation
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('directory');

        $this->panelTitle = Yii::t('DirectoryModule.base', '<strong>Directory</strong> menu');

        if ($module->isGroupListingEnabled()) {
            $this->addEntry(new MenuLink([
                'id' => 'directory-groups',
                'icon' => 'users',
                'label' => Yii::t('DirectoryModule.base', 'Groups'),
                'url' => ['/directory/directory/groups'],
                'sortOrder' => 100,
                'isActive' => MenuLink::isActiveState('directory', 'directory', 'groups')
            ]));
        }

        $this->addEntry(new MenuLink([
            'id' => 'directory-members',
            'icon' => 'user',
            'label' => Yii::t('DirectoryModule.base', 'Members'),
            'url' => ['/directory/directory/members'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('directory', 'directory', 'members')
        ]));

        $this->addEntry(new MenuLink([
            'id' => 'directory-spaces',
            'icon' => 'dot-circle-o',
            'label' => Yii::t('DirectoryModule.base', 'Spaces'),
            'url' => ['/directory/directory/spaces'],
            'sortOrder' => 300,
            'isActive' => MenuLink::isActiveState('directory', 'directory', 'spaces')
        ]));

        if ($module->showUserProfilePosts) {
            $this->addEntry(new MenuLink([
                'id' => 'directory-user-posts',
                'icon' => 'commenting ',
                'label' => Yii::t('DirectoryModule.base', 'User profile posts'),
                'url' => ['/directory/directory/user-posts'],
                'sortOrder' => 400,
                'isActive' => MenuLink::isActiveState('directory', 'directory', 'user-posts')
            ]));
        }

        parent::init();
    }

}
