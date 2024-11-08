<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use Yii;
use yii\helpers\Url;

/**
 * Space Administration Menu
 *
 * @author Luke
 */
class SpaceMenu extends TabMenu
{
    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.space', 'Overview'),
            'url' => Url::toRoute(['/admin/space/index']),
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('admin', 'space', 'index'),
            'isVisible' => Yii::$app->user->can(ManageSpaces::class),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.space', 'Settings'),
            'url' => Url::toRoute(['/admin/space/settings']),
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('admin', 'space', 'settings'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.space', 'Permissions'),
            'url' => Url::toRoute(['/admin/space/permissions']),
            'sortOrder' => 300,
            'isActive' => ControllerHelper::isActivePath('admin', 'space', 'permissions'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class),
        ]));

        parent::init();
    }

}
