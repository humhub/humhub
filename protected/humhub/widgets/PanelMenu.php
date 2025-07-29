<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\base\Widget;
use yii\helpers\BaseInflector;

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
     * Allow collapsing the HTML element having the `.collapse` class
     *
     * @since since 1.18
     */
    public bool $enableCollapseOption = true;

    /**
     * @deprecated since 1.18
     */
    public ?string $id = null;

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
            'enableCollapseOption' => $this->enableCollapseOption,
            'collapseId' => BaseInflector::slug(get_class($this->view->context)), // Generate a unique ID based from the parent Widget class name
            'extraMenus' => $this->extraMenus,
        ]);
    }
}
