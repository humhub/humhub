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
 * LoaderWidget adds a loader animation
 *
 * @package humhub.widgets
 * @since 0.20
 * @author Andreas Strobel
 */
class LoaderWidget extends \yii\base\Widget
{

    /**
     * id for DOM element
     *
     * @var string
     */
    public $id = "";

    /**
     * css classes for DOM element
     *
     * @var string
     */
    public $cssClass = "";
    
    /**
     * defines if the loader is initially shown
     */
    public $show = true;

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('loader', [
            'id' => $this->id,
            'cssClass' => $this->cssClass,
            'show' => $this->show
        ]);
    }

}

?>