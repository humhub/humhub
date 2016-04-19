<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space;

use humhub\modules\user\models\User;
use Yii;

/**
 * SpaceModule provides all space related classes & functions.
 *
 * @author Luke
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\space\controllers';

    /**
     * @var boolean Allow global admins (super admin) access to private content also when no member
     */
    public $globalAdminCanAccessPrivateContent = false;
    
    /**
     *
     * @var boolean Do not allow multiple spaces with the same name 
     */
    public $useUniqueSpaceNames = true;
    
    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof models\Space) {
            return [
                new permissions\InviteUsers(),
            ];
        } elseif ($contentContainer instanceof User) {
            return [];
        }

        return [
            new permissions\CreatePrivateSpace(),
            new permissions\CreatePublicSpace(),
        ];
    }
    
    public function getName()
    {
        return Yii::t('SpaceModule.base', 'Space');
    }

    /**
     * @inheritdoc
     */
    public function getNotifications() 
    {
       return [
           'humhub\modules\space\notifications\ApprovalRequest',
           'humhub\modules\space\notifications\ApprovalRequestAccepted',
           'humhub\modules\space\notifications\ApprovalRequestDeclined',
           'humhub\modules\space\notifications\Invite',
           'humhub\modules\space\notifications\InviteAccepted',
           'humhub\modules\space\notifications\InviteDeclined'
       ];
    }

}
