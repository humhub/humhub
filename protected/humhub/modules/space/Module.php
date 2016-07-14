<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space;

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
                new permissions\CreatePublicContent,
            ];
        }

        return [
            new permissions\CreatePrivateSpace(),
            new permissions\CreatePublicSpace(),
        ];
    }

}
