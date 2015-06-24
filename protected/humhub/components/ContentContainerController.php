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
 * ContainerController is the base controller for all space or user profile
 * controllers.
 * 
 * It automatically detects with the help of sguid (Space) or uguid (User) request
 * parameter the underlying HActiveRecordContentContainer (User/Space) and set
 * its model to the contentContainer Attribute.
 * 
 * In case of space also the SpaceControllerBehavior is automatically attached.
 * In case of user the ProfileControllerBehavior is also automatically attached.
 *
 * By using createContainerUrl method instead of createUrl the sguid/uguid parameter
 * is automatically added to url.
 * 
 * @package humhub.components
 * @since 0.6
 */
class ContentContainerController extends Controller
{

    /**
     * ContentContainer
     * 
     * @var HActiveRecordContentContainer 
     */
    public $contentContainer = null;

    /**
     * Automatically checks permission to access the container
     * before a controller action is called.
     */
    public $autoCheckContainerAccess = true;

    /**
     * Hides containers sidebar in containers layout
     * 
     * @since 0.11
     */
    public $hideSidebar = false;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@', (HSetting::Get('allowGuestAccess', 'authentication_internal')) ? "?" : "@"),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Automatically loads the underlying contentContainer (User/Space) by using
     * the uguid/sguid request parameter
     * 
     * @return boolean
     */
    public function init()
    {

        $spaceGuid = Yii::app()->request->getParam('sguid', '');
        $userGuid = Yii::app()->request->getParam('uguid', '');

        if ($spaceGuid != "") {

            $this->contentContainer = Space::model()->findByAttributes(array('guid' => $spaceGuid));

            if ($this->contentContainer == null) {
                throw new CHttpException(404, Yii::t('base', 'Space not found!'));
            }

            $this->attachBehavior('SpaceControllerBehavior', array(
                'class' => 'application.modules_core.space.behaviors.SpaceControllerBehavior',
                'space' => $this->contentContainer
            ));

            Yii::app()->params['currentSpace'] = $this->contentContainer;

            $this->subLayout = "application.modules_core.space.views.space._layout";
        } elseif ($userGuid != "") {

            $this->contentContainer = User::model()->findByAttributes(array('guid' => $userGuid));

            if ($this->contentContainer == null) {
                throw new CHttpException(404, Yii::t('base', 'User not found!'));
            }

            $this->attachBehavior('ProfileControllerBehavior', array(
                'class' => 'application.modules_core.user.behaviors.ProfileControllerBehavior',
                'user' => $this->contentContainer
            ));

            Yii::app()->params['currentUser'] = $this->contentContainer;

            $this->subLayout = "application.modules_core.user.views.profile._layout";
        } else {
            throw new CHttpException(500, Yii::t('base', 'Could not determine content container!'));
        }

        /**
         * Auto check access rights to this container
         */
        if ($this->contentContainer != null) {
            if ($this->autoCheckContainerAccess) {
                $this->checkContainerAccess();
            }
        }

        if (!$this->checkModuleIsEnabled()) {
            throw new CHttpException(405, Yii::t('base', 'Module is not on this content container enabled!'));
        }


        return parent::init();
    }

    /**
     * Creates a relative URL for the specified action defined in this controller.
     * The container guid (sguid/uguid) attribute is automatically added to the 
     * constructed url.
     * 
     * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
     * If the ControllerID is not present, the current controller ID will be prefixed to the route.
     * If the route is empty, it is assumed to be the current action.
     * If the controller belongs to a module, the {@link CWebModule::getId module ID}
     * will be prefixed to the route. (If you do not want the module ID prefix, the route should start with a slash '/'.)
     * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
     * If the name is '#', the corresponding value will be treated as an anchor
     * and will be appended at the end of the URL.
     * @param string $ampersand the token separating name-value pairs in the URL.
     * @return string the constructed URL
     */
    public function createContainerUrl($route, $params = array(), $ampersand = '&')
    {
        return $this->contentContainer->createUrl($route, $params, $ampersand);
    }

    /**
     * Checks if current user can access current ContentContainer by using 
     * underlying behavior ProfileControllerBehavior/SpaceControllerBehavior.
     * 
     * If access check failed, an CHttpException is thrown.
     */
    public function checkContainerAccess()
    {
        if ($this->contentContainer instanceof User) {
            $this->getOwner()->checkAccess();
        } elseif ($this->contentContainer instanceof Space) {
            $this->getOwner()->checkAccess();
        }
    }

    /**
     * Checks if current module is enabled on this content container.
     * 
     * @return boolean
     */
    public function checkModuleIsEnabled()
    {
        $module = $this->getModule();
        if ($module != null && $module instanceof HWebModule && !$module->isCoreModule) {
            return $this->contentContainer->isModuleEnabled($module->getId());
        }
        return true;
    }

}
