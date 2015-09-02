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
 * HEditorWidget add users to posts and comments
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Andreas Strobel
 */
class HEditorWidget extends HWidget
{

    /**
     * Id of input element which should replaced
     *
     * @var string
     */
    public $id = "";

    /**
     * JSON Search URL
     */
    public $searchUrl = "//search/mentioning";


    public $inputContent = "";


    /**
     * Inits the widget
     *
     */
    public function init()
    {

        // load resources
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/resources/at', true, 0, defined('YII_DEBUG'));
        
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.caret.min.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.atwho.min.js');
        Yii::app()->clientScript->registerCssFile($assetPrefix . '/jquery.atwho.css');

        $this->inputContent = HHtml::translateEmojis($this->inputContent);
        $this->inputContent = HHtml::translateMentioning($this->inputContent);
        $this->inputContent = nl2br($this->inputContent);

    }

    public function run() {

        // render heditor view
        $this->render('heditor', array('id' => $this->id, 'userSearchUrl' => $this->searchUrl, 'inputContent' => $this->inputContent));

    }

}
