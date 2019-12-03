<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;


/**
 * Group Administration Menu
 */
class InformationMenu extends TabMenu
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.information', 'About HumHub'),
            'url' => ['/admin/information/about'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'information', 'about')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.information', 'Prerequisites'),
            'url' => ['/admin/information/prerequisites'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', 'information', 'prerequisites')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.information', 'Database'),
            'url' => ['/admin/information/database'],
            'sortOrder' => 300,
            'isActive' => MenuLink::isActiveState('admin', 'information', 'database')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.information', 'Background Jobs'),
            'url' => ['/admin/information/background-jobs'],
            'sortOrder' => 400,
            'isActive' => MenuLink::isActiveState('admin', 'information', 'background-jobs')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.information', 'Logging'),
            'url' => ['/admin/logging'],
            'sortOrder' => 500,
            'isActive' => MenuLink::isActiveState('admin', 'logging')
        ]));

        parent::init();
    }

}
