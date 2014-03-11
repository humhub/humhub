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
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 *
 * @package humhub.components
 * @since 0.5
 */
class Controller extends CController {

    /**
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/main';

    /**
     * @var string the sub layout for the controller view. Defaults to '',
     */
    public $subLayout = '';

    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();
    public $snippets = array();

    /**
     * Minimum required database version (HSetting)
     *
     * @var type
     */
    #protected $requiredDbVersion = 28;

    /**
     * Inits the controller class.
     *
     * - Force Installer when not HumHub is not installed yet.
     * - Registers Event Interceptor
     * - Loads Basic HSetting
     * - Set Language
     * - Check Database Version
     */
    public function init() {

        Yii::app()->interceptor->intercept($this);

        // Force installer, when not installed
        if (!Yii::app()->params['installed']) {
            if ($this->getModule() != null && $this->getModule()->id == "installer") {
                return parent::init();
            }
            $this->redirect(array('//installer/index'));
        }

        // Switch to correct user language
        if (Yii::app()->user->language) {
            Yii::app()->language = Yii::app()->user->language;
        }

        // Enable Jquery Globally
        Yii::app()->clientScript->registerCoreScript('jquery');

        // Tweaks for Ajax Requests
        Yii::app()->user->loginRequiredAjaxResponse = "<script>window.location.href = '" . Yii::app()->user->id . "';</script>";
        if (Yii::app()->request->isAjaxRequest) {
            Yii::app()->clientScript->scriptMap = array(
                'jquery.js' => false,
                'jquery.min.js' => false,
            );
        }

        // Set a javascript variable which holds the Name and the Value of the CSRF Variable.
        Yii::app()->clientScript->setJavascriptVariable('csrfName', Yii::app()->request->csrfTokenName);
        Yii::app()->clientScript->setJavascriptVariable('csrfValue', Yii::app()->request->csrfToken);
        Yii::app()->clientScript->setJavascriptVariable('baseUrl', Yii::app()->getBaseUrl(true));

        return parent::init();
    }

    /**
     * Create Redirect for AJAX Requests which output goes into HTML content.
     * Is an alternative method to redirect, for ajax responses.
     */
    public function htmlRedirect($url = "") {

        echo "<script>\n";

        // If current URL == New Url (absolute || relative) then only Refresh
        echo "if (window.location.pathname+window.location.search+window.location.hash == '" . $url . "' || '" . $url . "' == window.location.href) { \n";

        //echo "window.location.reload();\n"; // Drops warning on Posts
        // Remove test.php#xy  (#xy) part
        $temp = explode("#", $url);
        $url = $temp[0];

        echo "if (window.location.search == '') {\n";
        echo "window.location.href = '" . $url . "?c=" . time() . "';\n";
        echo "} else { \n";
        echo "window.location.href = '" . $url . "&c=" . time() . "';\n";
        echo "} \n";

        // When completly new url, set new window location
        echo "} else { \n";
        echo "window.location.href = '" . $url . "';\n";
        echo "} \n";
        echo "</script>\n";
        Yii::app()->end();
    }

    /**
     * Outputs a given JSON Array and ends the application
     *
     * @param type $json
     */
    protected function renderJson($json) {
        echo CJSON::encode($json);
        Yii::app()->end();
        return;
    }

    /**
     * Ensures the current request is a post, when not throw an error
     */
    public function forcePostRequest() {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(500, Yii::t('base', 'Invalid request.'));
        }

        return true;
    }

    /**
     * Closes a modal
     */
    public function renderModalClose() {

        // close modal to hide the loaded view, which is visible for some seconds, after creation
        echo "<script>";
        echo "$('#globalModal').modal('hide')";
        echo "</script>";
        //Yii::app()->end();
    }

    /**
     * Add a JavaScript to the renderPartial method to fire an event at the body tag, when the view loaded successfully
     */
    public function renderPartial($view, $data = null, $return = false, $processOutput = false) {

        if (Yii::app()->request->isAjaxRequest) {
            /**
             * Fire an event with the following params:
             * @param1 String controllerID
             * @param2 String moduleID
             * @param3 String actionID
             * @param4 String view path
             */
            Yii::app()->clientScript->registerScript("autoAjaxEventFire","$('body').trigger('ajaxLoaded', ['". $this->id."', '". $this->module->id ."', '". $this->action->id ."', '". $view ."']);",CClientScript::POS_END);
        }

        return parent::renderPartial($view, $data, $return, $processOutput);
    }


}
