<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\ui\menu\MenuImage;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;

/**
 * The Main Navigation for a space. It includes the Modules the Stream
 *
 * @author Luke
 * @since 0.5
 */
class Menuroom extends LeftNavigation
{

    /** @var Space */
    public $space;

    /** @var Space */
    public $id = 'space-main-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {
//        if (!$this->space) {
//            $this->space = ContentContainerHelper::getCurrent(Space::class);
//        }

        if (!$this->space) {
            throw new Exception('Could not instance space menu without space!');
        }

        $this->panelTitle = Yii::t('SpaceModule.base', '<strong>My</strong> room');
        parent::init();
        // For private Spaces without membership, show only the About Page in the menu.
        // This is necessary for the invitation process otherwise there is no access in this case anyway.
        $this->addEntry(new MenuImage([]));
        /** @var Module $module */
    }
}
