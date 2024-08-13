<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\base\Widget;

/**
 * PanelMenuWidget add an dropdown menu to the panel header
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Andreas Strobel
 */
class PanelMenu extends Widget
{

    /**
     * @var String unique id from panel element
     */
    public $id = '';

    /**
     * Workaround to inject menu items to PanelMenu
     *
     * @deprecated since version 0.9
     * @internal description
     * @var String
     */
    public $extraMenus = '';


    /**
     * @inheritDoc
     */
    public function init()
    {
        return parent::init();
    }

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('panelMenu', [
            'id' => $this->id,
            'extraMenus' => $this->extraMenus,
        ]);
    }

}
