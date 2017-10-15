<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\contentcontainer\components;

use Yii;
use yii\web\HttpException;
use humhub\modules\content\models\ContentContainer;
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
 * @since 1.3
 */
class Controller extends \humhub\components\Controller
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

        // ToDo: If the controller is from a module, which is not enabled in the current contentContainer, throw error

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAccess()
    {
        return new ContentContainerControllerAccess(['contentContainer' => $this->contentContainer]);
    }

}
