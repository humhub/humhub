<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\SubTabMenu;
use Yii;
use yii\helpers\Url;

/**
 * Group Administration Menu
 */
class GroupMenu extends SubTabMenu
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Overview'),
            'url' => ['/admin/group/index'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'group', 'index'),
        ]));
        parent::init();
    }

}
