<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use yii\web\HttpException;
use humhub\components\Controller;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerControllerAccess;

/**
 * Controller is the base class of web controllers which acts in scope of a ContentContainer (e.g. Space or User).
 * 
 * To automatically load the current contentcontainer the containers guid must be passed as GET parameter 'cguid'.
 * You can create URLs in the scope of an ContentContainer by passing the contentContainer instance as 'container' or 'contentContainer'
 * as parameter to the URLManager.
 * 
 * Example:
 * 
 * ```
 * $url = Url::to(['my/action', 'container' => $this->contentContainer');
 * ```
 * 
 * Based on the current ContentContainer a behavior (defined in ContentContainerActiveRecord::controllerBehavior) will be automatically
 * attached to this controller instance.

 * The attached behavior will perform basic access checks, adds the container sublayout and perform other tasks 
 * (e.g. the space behavior will update the last visit membership attribute).
 * 
 * @see \humhub\modules\space\behaviors\SpaceController
 * @see \humhub\modules\user\behaviors\ProfileController
 */
class ContentContainerController extends Controller
{

    /**
     * Specifies if a contentContainer (e.g. Space or User) is required to run this controller.
     * Set this to false, if your controller should also act on global scope.
     * 
     * @var boolean require cguid container parameter 
     */
    public $requireContainer = true;

    /**
     * @var ContentContainerActiveRecord the content container (e.g. Space or User record)
     */
    public $contentContainer = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Load the ContentContainer
        $guid = Yii::$app->request->get('cguid', Yii::$app->request->get('sguid', Yii::$app->request->get('uguid')));
        if (!empty($guid)) {
            $contentContainerModel = ContentContainer::findOne(['guid' => $guid]);
            if ($contentContainerModel !== null) {
                $this->contentContainer = $contentContainerModel->getPolymorphicRelation();
            }
        }

        if ($this->requireContainer && $this->contentContainer === null) {
            throw new HttpException('Could not find content container!');
        }

        if ($this->contentContainer !== null && $this->contentContainer->controllerBehavior) {
            $this->attachBehavior('containerControllerBehavior', ['class' => $this->contentContainer->controllerBehavior]);
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Directly redirect guests to login page - if guest access isn't enabled
        if (Yii::$app->user->isGuest && Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') != 1) {
            Yii::$app->user->loginRequired();
            return false;
        }

        $this->checkModuleIsEnabled();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAccess()
    {
        return new ContentContainerControllerAccess(['contentContainer' => $this->contentContainer]);
    }

    /**
     * Checks if the requested module is available in this contentContainer.
     * 
     * @throws HttpException if the module is not enabled
     */
    protected function checkModuleIsEnabled()
    {
        if ($this->module instanceof ContentContainerModule && $this->contentContainer !== null &&
                !$this->contentContainer->moduleManager->isEnabled($this->module->id)) {
            throw new HttpException(405, Yii::t('base', 'Module is not enabled on this content container!'));
        }
    }

}
