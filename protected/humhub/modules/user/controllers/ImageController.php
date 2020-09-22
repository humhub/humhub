<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\modules\content\controllers\ContainerImageController;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use Yii;
use yii\web\HttpException;

/**
 * ImageController handles user profile or user banner image modifications
 *
 * @since 1.2
 * @author Luke
 */
class ImageController extends ContainerImageController
{
    public $validContentContainerClasses = [User::class];

    public function init()
    {
        $legacyUserGuid = Yii::$app->request->get('userGuid');

        if($legacyUserGuid) {
            $this->validContentContainerClasses = null;
            $this->requireContainer = false;
        }

        parent::init();

        if($legacyUserGuid) {
            $contentContainerModel = ContentContainer::findOne(['guid' => $legacyUserGuid]);
            if ($contentContainerModel !== null) {
                $this->contentContainer = $contentContainerModel->getPolymorphicRelation();
            }

            if(!$this->contentContainer) {
                throw new HttpException(404);
            }
        }
    }

    public function getAccessRules()
    {
        return [
            ['validateAccess'],
        ];
    }

    public function validateAccess($rule, $access)
    {
        if(!static::canEditProfileImage($this->contentContainer)) {
            $access->code = 401;
            $access->reason = 'Not authorized!';
            return false;
        }

        return true;
    }

    public static function canEditProfileImage(User $userProfile)
    {
        if(Yii::$app->user->isGuest) {
            return false;
        }

        if($userProfile->is(Yii::$app->user->getIdentity())) {
            return true;
        }

        return (Yii::$app->user->isAdmin() && Yii::$app->getModule('user')->adminCanChangeUserProfileImages);
    }
}
