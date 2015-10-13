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
class Controller extends EController
{

    public $layout = '//layouts/main';

    /**
     * @var string the sub layout for the controller view. Defaults to '',
     */
    public $subLayout = '';
    private $_pageTitle;

    /**
     * Inits the controller class.
     *
     * - Force Installer when not HumHub is not installed yet.
     * - Registers Event Interceptor
     * - Loads Basic HSetting
     * - Set Language
     * - Check Database Version
     */
    public function init()
    {

        Yii::app()->interceptor->intercept($this);

        $this->handleLocale();

        // Force installer, when not installed
        if (!Yii::app()->params['installed']) {
            if ($this->getModule() != null && $this->getModule()->id == "installer") {
                return parent::init();
            }
            $this->redirect(array('//installer/index'));
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

        $this->initAjaxCsrfToken();

        return parent::init();
    }

    /**
     * Create Redirect for AJAX Requests which output goes into HTML content.
     * Is an alternative method to redirect, for ajax responses.
     */
    public function htmlRedirect($url = "")
    {

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

    // this function will work to post csrf token.
    protected function initAjaxCsrfToken()
    {

        Yii::app()->clientScript->registerScript('AjaxCsrfToken', ' $.ajaxSetup({
			data: {"' . Yii::app()->request->csrfTokenName . '": "' . Yii::app()->request->csrfToken . '"},
			cache:false
			});', CClientScript::POS_HEAD);
    }

    /**
     * Outputs a given JSON Array and ends the application
     *
     * @param Array $json
     */
    protected function renderJson($json = array(), $success = true)
    {

        if (is_array($json) && !isset($json['success']))
            $json['success'] = $success;

        echo CJSON::encode($json);
        Yii::app()->end();
        return;
    }

    /**
     * Ensures the current request is a post, when not throw an error
     */
    public function forcePostRequest()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(500, Yii::t('base', 'Invalid request.'));
        }

        return true;
    }

    /**
     * Closes a modal
     */
    public function renderModalClose()
    {

        // close modal to hide the loaded view, which is visible for some seconds, after creation
        echo "<script>";
        echo "$('#globalModal').modal('hide');";
        echo "</script>";
        //Yii::app()->end();
    }

    /**
     * Add a JavaScript to the renderPartial method to fire an event at the body tag, when the view loaded successfully
     */
    public function renderPartial($view, $data = null, $return = false, $processOutput = false)
    {

        if (Yii::app()->request->isAjaxRequest) {

            // get module id if exists
            $moduleID = "";
            if ($this->module != null) {
                $moduleID = $this->module->id;
            }

            /**
             * Fire an event with the following params:
             * @param1 String controllerID
             * @param2 String moduleID
             * @param3 String actionID
             * @param4 String view path
             */
            Yii::app()->clientScript->registerScript("autoAjaxEventFire", "$('body').trigger('ajaxLoaded', ['" . $this->id . "', '" . $moduleID . "', '" . $this->action->id . "', '" . $view . "']);", CClientScript::POS_END);
        }

        return parent::renderPartial($view, $data, $return, $processOutput);
    }

    /**
     * @return string the page title. Defaults to the controller name and the action name.
     */
    public function getPageTitle()
    {
        if ($this->_pageTitle !== null) {
            // StripTags because we often use <strong> in headlines
            return strip_tags($this->_pageTitle) . " - " . Yii::app()->name;
        } else {
            return Yii::app()->name;
        }
    }

    /**
     * @param string $value the page title.
     */
    public function setPageTitle($value)
    {
        $this->_pageTitle = $value;
    }

    public function getViewFile($viewName)
    {

        // Adds simple themeing support to console applications
        if (Yii::app() instanceof CConsoleApplication) {
            if (Yii::app()->theme && Yii::app()->theme != "") {
                $themeName = Yii::app()->theme;

                if (strpos($viewName, '.')) {
                    // Replace application.modules[_core].MODULEID.widgets.views
                    //      in
                    //          webroot.themes.CURRENTTHEME.views.MODULEID.widgets
                    $viewNameTheme = $viewName;
                    $viewNameTheme = str_replace('application.views.', 'webroot.themes.' . $themeName . '.views.', $viewNameTheme);
                    $viewNameTheme = preg_replace('/application\.modules(?:_core)?\.(.*?)\.views\.(.*)/i', 'webroot.themes.' . $themeName . '.views.\1.\2', $viewNameTheme);
                    $viewFile = Yii::getPathOfAlias($viewNameTheme);
                    if (is_file($viewFile . '.php')) {
                        return Yii::app()->findLocalizedFile($viewFile . '.php');
                    }
                }
            }
        }

        return parent::getViewFile($viewName);
    }

    protected function handleLocale()
    {

        $isGuest = (!Yii::app()->params['installed'] || Yii::app()->user->isGuest);

        if ($isGuest) {

            // Choose Language Form Submitted?
            if (isset($_POST['ChooseLanguageForm'])) {
                $languageModel = new ChooseLanguageForm();
                $languageModel->attributes = $_POST['ChooseLanguageForm'];
                if ($languageModel->validate()) {
                    Yii::app()->request->cookies['language'] = new CHttpCookie('language', $languageModel->language);
                }
            }

            $language = Yii::app()->request->getPreferredAvailableLanguage();

            if (isset(Yii::app()->request->cookies['language'])) {
                $language = (string) Yii::app()->request->cookies['language'];
                if (!array_key_exists($language, Yii::app()->params['availableLanguages'])) {
                    Yii::app()->request->cookies['language'] = new CHttpCookie('language', 'en');
                    $language = 'en';
                }
            }

            if ($language != "") {
                Yii::app()->setLanguage($language);
            }
        } elseif (Yii::app()->user->language) {
            Yii::app()->setLanguage(Yii::app()->user->language);
        }

        Yii::app()->clientScript->setJavascriptVariable('localeId', Yii::app()->locale->id);

        // Temporary force set the system timezone to avoid php 5.5+ warnings until we create an admin/config option for that
        date_default_timezone_set(@date_default_timezone_get());
    }

}
