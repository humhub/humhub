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
 * TopMenuWidget is the primary top navigation class extended from MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Luke
 */
class TopMenuWidget extends MenuWidget {

    /**
     * @var String template to use
     */
    public $template = "application.widgets.views.topNavigation";

    /**
     * Inits the Top Navigation by adding some default items
     */
    public function init() {

        $this->addItem(array(
            'label' => Yii::t('base', 'Dashboard'),
            'url' => Yii::app()->createUrl('//dashboard/index'),
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->id == "dashboard"),
        ));

        /*
          $this->addItem(array(
          'label' => Yii::t('base', 'Spaces'),
          'url' => Yii::app()->createUrl('//space/browse'),
          'sortOrder' => 200,
          'isActive' => ((Yii::app()->controller->module && Yii::app()->controller->module->id == "space") || Yii::app()->params['currentSpace'] != null),
          ));
         */

        /*
          $this->addItem(array(
          'label' => Yii::t('base', 'Messages'),
          'url' => Yii::app()->createUrl('//mail/mail/index'),
          'sortOrder' => 300,
          'isActive' => (Yii::app()->controller->id == "mail"),
          ));
         */

        parent::init();
    }

}

?>
