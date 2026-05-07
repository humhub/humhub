<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\modules\user\models\forms\AccountSettings;
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
     * Allow hiding the panel
     *
     * Requires $panelLabel not to be empty
     *
     * @since since 1.19
     */
    public bool $enableHideOption = true;

    /**
     * Optional unique ID of the panel
     *
     * Used for local storage state (expanded/collapsed or hidden)
     *
     * If the parent widget class is unique, it can be null
     *
     * @since since 1.19
     */
    public ?string $panelId = null;

    /**
     * Optional Label of the panel (usually the panel header title)
     *
     * Used to show hidden panels from the User Account Settings
     *
     * @since since 1.19
     */
    public ?string $panelLabel = null;

    /**
     * Workaround to inject menu items to PanelMenu
     *
     * @deprecated since version 0.9
     * @internal description
     * @var String
     */
    public $extraMenus = '';

    public function init()
    {
        parent::init();

        $class = $this->view->context::class;

        // Generate a unique ID from the parent Widget class name
        $this->panelId ??= BaseInflector::slug($class);

        if ($this->panelLabel) {
            $this->panelLabel = strip_tags($this->panelLabel);
        } else {
            $this->enableHideOption = false;
        }
    }

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('panelMenu', [
            'enableCollapseOption' => $this->enableCollapseOption,
            'enableHideOption' => $this->enableHideOption,
            'hidePanel' => AccountSettings::isHiddenPanel($this->panelId),
            'panelId' => $this->panelId,
            'panelLabel' => $this->panelLabel,
            'extraMenus' => $this->extraMenus,
        ]);
    }
}
