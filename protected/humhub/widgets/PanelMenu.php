<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\widgets;

/**
 * PanelMenuWidget add an dropdown menu to the panel header
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Andreas Strobel
 */
class PanelMenu extends \yii\base\Widget
{

    /**
     * @var String unique id from panel element
     */
    public $id = "";

    /**
     * Workaround to inject menu items to PanelMenu
     *
     * @deprecated since version 0.9
     * @internal description
     * @var String
     */
    public $extraMenus = "";

    public function init()
    {
        return parent::init();
    }

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('panelMenu', array(
                    'id' => $this->id,
                    'extraMenus' => $this->extraMenus,
        ));
    }

}

?>