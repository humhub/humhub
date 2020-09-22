<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use Yii;

/**
 * Space Administration Menu
 *
 * @author Luke
 */
class SecurityTabMenu extends TabMenu
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
            'label' => Yii::t('AdminModule.base', 'General'),
            'url' => $this->space->createUrl('/space/manage/security'),
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState(null, 'security', 'index'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Permissions'),
            'url' => $this->space->createUrl('/space/manage/security/permissions'),
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState(null, 'security', 'permissions'),
        ]));

        parent::init();
    }

}
