<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\controllers\ContainerImageController;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use Yii;
use yii\db\IntegrityException;
use yii\web\HttpException;

/**
 * ImageController handles user profile or user banner image modifications
 *
 * @since 1.2
 * @author Luke
 *
 * @property-read array[] $accessRules
 */
class ImageController extends ContainerImageController
{
    public $validContentContainerClasses = [User::class];

    /**
     * @throws IntegrityException
     * @throws HttpException
     */
    public function init()
    {
        $legacyUserGuid = Yii::$app->request->get('userGuid');

        if ($legacyUserGuid) {
            $this->validContentContainerClasses = null;
            $this->requireContainer = false;
        }

        parent::init();

        if ($legacyUserGuid) {
            $contentContainerModel = ContentContainer::findOne(['guid' => $legacyUserGuid]);
            if ($contentContainerModel !== null) {
                $this->contentContainer = $contentContainerModel->getPolymorphicRelation();
            }

            if (!$this->contentContainer) {
                throw new HttpException(404);
            }
        }
    }

    public function getAccessRules(): array
    {
        return [
            ['validateAccess'],
        ];
    }

    public function validateAccess($rule, $access): bool
    {
        if (!$this->contentContainer instanceof User || !static::canEditProfileImage($this->contentContainer)) {
            $access->code = 401;
            $access->reason = 'Not authorized!';
            return false;
        }

        return true;
    }

    public static function canEditProfileImage(User $userProfile): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        /** @var User $user */
        $user = Yii::$app->user->getIdentity();

        if ($userProfile->is($user)) {
            return true;
        }

        if (Yii::$app->getModule('user')->adminCanChangeUserProfileImages && Yii::$app->user->can(ManageUsers::class)) {
            return true;
        }

        return false;
    }
}
