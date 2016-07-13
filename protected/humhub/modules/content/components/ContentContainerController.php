<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use yii\web\HttpException;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * ContainerController is the base controller for all space or user profile controllers.
 *
 * It automatically detects the Container by request parameters.
 * Use [[ContentContainerActiveCreated::createUrl]] method to generate URLs.
 *
 * e.g. $this->contentContainer->createUrl();
 *
 * Depends on the loaded the Container Type a Behavior with additional methods will be attached.
 * - Space  \humhub\modules\space\behaviors\SpaceController
 * - User attached Behavior: \humhub\modules\user\behaviors\ProfileController
 *
 * @since 0.6
 */
class ContentContainerController extends Controller
{

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer = null;

    /**
     * @var boolean automatic check user access permissions to this container
     */
    public $autoCheckContainerAccess = true;

    /**
     * @var boolean hides containers sidebar in layout
     * @since 0.11
     */
    public $hideSidebar = false;

    /**
     * Automatically loads the underlying contentContainer (User/Space) by using
     * the uguid/sguid request parameter
     *
     * @return boolean
     */
    public function init()
    {
        $spaceGuid = Yii::$app->request->get('sguid', '');
        $userGuid = Yii::$app->request->get('uguid', '');

        if ($spaceGuid != "") {

            $this->contentContainer = Space::findOne(['guid' => $spaceGuid]);

            if ($this->contentContainer == null) {
                throw new \yii\web\HttpException(404, Yii::t('base', 'Space not found!'));
            }

            $this->attachBehavior('SpaceControllerBehavior', array(
                'class' => \humhub\modules\space\behaviors\SpaceController::className(),
                'space' => $this->contentContainer
            ));
            $this->subLayout = "@humhub/modules/space/views/space/_layout";
        } elseif ($userGuid != "") {

            $this->contentContainer = User::findOne(['guid' => $userGuid]);

            if ($this->contentContainer == null) {
                throw new \yii\web\HttpException(404, Yii::t('base', 'User not found!'));
            }

            $this->attachBehavior('ProfileControllerBehavior', [
                'class' => \humhub\modules\user\behaviors\ProfileController::className(),
                'user' => $this->contentContainer
            ]);

            $this->subLayout = "@humhub/modules/user/views/profile/_layout";
        } else {
            throw new \yii\web\HttpException(500, Yii::t('base', 'Could not determine content container!'));
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
            throw new HttpException(405, Yii::t('base', 'Module is not enabled on this content container!'));
        }

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        if (parent::beforeAction($action)) {

            // Directly redirect guests to login page - if guest access isn't enabled
            if (Yii::$app->user->isGuest && Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') != 1) {
                Yii::$app->user->loginRequired();
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Checks if current user can access current ContentContainer by using
     * underlying behavior ProfileControllerBehavior/SpaceControllerBehavior.
     *
     * If access check failed, an CHttpException is thrown.
     */
    public function checkContainerAccess()
    {
        // Implemented by behavior
        $this->checkAccess();
    }

    /**
     * Checks if current module is enabled on this content container.
     *
     * @todo Also support submodules
     * @return boolean is current module enabled
     */
    public function checkModuleIsEnabled()
    {
        if ($this->module instanceof ContentContainerModule && $this->contentContainer !== null) {
            return $this->contentContainer->isModuleEnabled($this->module->id);
        }

        return true;
    }

}
