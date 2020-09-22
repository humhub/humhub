<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\SubTabMenu;
use Yii;
use yii\helpers\Url;

/**
 * Authentication Settings Menu
 */
class AuthenticationMenu extends SubTabMenu
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.settings', 'General'),
            'url' => ['/admin/authentication'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'authentication', 'index'),
        ]));

        parent::init();
    }

}
