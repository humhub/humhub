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
     * Unique ID of the section to collapse via the collapse/expand menu item.
     *
     * If empty, the collapse/expand menu item is not rendered.
     *
     * @since since 1.18
     */
    public ?string $collapseId = null;

    /**
     * @deprecated since 1.18
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
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('panelMenu', [
            'collapseId' => $this->collapseId,
            'extraMenus' => $this->extraMenus,
        ]);
    }

}
