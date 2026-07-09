<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\modules\ui\menu\widgets\Menu;
use humhub\modules\user\helpers\AuthHelper;
use Yii;

/**
 * TopMenuWidget is the primary top navigation class extended from MenuWidget.
 *
 * @since 0.5
 * @author Luke
 */
class TopMenu extends Menu
{
    /**
     * @inheritdoc
     */
    public $id = 'top-menu-nav';

    /**
     * @inheritdoc
     */
    public $template = 'topNavigation';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Don't show top menu if guest access is disabled
        if (Yii::$app->user->isGuest && !AuthHelper::isGuestAccessEnabled()) {
            $this->template = '';
        }
    }



}
