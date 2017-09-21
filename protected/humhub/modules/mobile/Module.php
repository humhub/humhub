<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\mobile;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;

/**
 * Mobile Module
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\mobile\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'index';

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @inheritdoc
     */
    public $resourcesPath = 'resources';


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('MobileModule.base', 'Mobile');
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        // E.g: Permission if user is allowed to access with mobile app?
        if ($contentContainer instanceof Space) {
            return [];
        } elseif ($contentContainer instanceof User) {
            return [];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNotifications()
    {
        // E.g: A new device was registered to your account, if this is not your device,...
        if (Yii::$app->user->isAdmin()) {
            return [];
        }

        return [];
    }

}
