<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;

/**
 * Space Administration Menu
 *
 * @author Luke
 */
class DefaultMenu extends TabMenu
{

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Basic'),
            'url' => $this->space->createUrl('/space/manage/default/index'),
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState(null, 'default', 'index')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Advanced'),
            'url' => $this->space->createUrl('/space/manage/default/advanced'),
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState(null, 'default', 'advanced')
        ]));

        parent::init();
    }

}
