<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Yii;
use humhub\components\behaviors\AccessControl;

/**
 * BaseAccountController is the base controller for user account (settings) pages
 *
 * @since 1.1
 * @author luke
 */
class BaseAccountController extends \humhub\components\Controller
{

    public $subLayout = "@humhub/modules/user/views/account/_layout";

    public function init() {
        $this->appendPageTitle(\Yii::t('UserModule.base', 'My Account'));
        return parent::init();
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
            ]
        ];
    }

    /**
     * Returns the current user of this account
     * 
     * @return \humhub\modules\user\models\User
     */
    public function getUser()
    {
        return Yii::$app->user->getIdentity();
    }

}
