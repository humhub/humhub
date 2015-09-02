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
 * Created by PhpStorm.
 * User: Struppi
 * Date: 18.12.13
 * Time: 08:24
 */
class SearchMenuWidget extends HWidget {

    public function init() {
        // publish resource files
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/searchmenu.js');
        Yii::app()->clientScript->setJavaScriptVariable('searchAjaxUrl', $this->createUrl('//search/index', array('mode'=>'quick', 'keyword'=>'-searchKeyword-')));
    }

    /**
     * Displays / Run the Widgets
     */
    public function run() {
        $this->render('searchMenu', array());
    }

}
