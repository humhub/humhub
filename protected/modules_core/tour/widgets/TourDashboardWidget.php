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

/**
 * @author andystrobel
 */
class TourDashboardWidget extends HWidget
{

    public function run()
    {

        print '<script>console.log("Häh: '.Yii::app()->user->getModel()->getSetting("hideTourPanel", "tour").'");</script>';

        // check if tour is activated for new users
        if (HSetting::Get('enable', 'tour') == 1) {

            // save in variable, if the tour panel is activated or not
            $hideTourPanel = Yii::app()->user->getModel()->getSetting("hideTourPanel", "tour");

            // if panel is not deactivated...
            if ($hideTourPanel == 0) {

                // get the first space in database (should be the welcome space)
                $space = Space::model()->find();

                // ...render view
                $this->render('tourPanel', array('space' => $space));

            }

        }
    }

}
