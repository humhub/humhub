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
 * HActiveRecordBehavior is the base behavior for all Active Record Behaviors.
 *
 * @package humhub.behaviors
 * @since 0.5
 */
class HActiveRecordBehavior extends CActiveRecordBehavior
{

    /**
     * On after construct event of an active record
     *
     * @param type $event
     */
    public function afterConstruct($event)
    {

        // Intercept this controller
        Yii::app()->interceptor->intercept($this);

        parent::afterConstruct($event);
    }

}

?>
