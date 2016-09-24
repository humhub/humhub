<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin;

use Yii;

/**
 * Admin Module
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\admin\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'index';

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @var boolean is marketplace enabled?
     */
    public $marketplaceEnabled = true;
    
    
    /**
     * @var boolean check daily for new HumHub version
     */
    public $dailyCheckForNewVersion = true;
    
    public function getName()
    {
        return Yii::t('AdminModule.base', 'Admin');
    }

    /**
     * @inheritdoc
     */
    public function getNotifications() 
    {
        if(Yii::$app->user->isAdmin()) {
            return [
                'humhub\modules\user\notifications\Followed',
                'humhub\modules\user\notifications\Mentioned'
            ];
        } 
        return [];
    }
}
