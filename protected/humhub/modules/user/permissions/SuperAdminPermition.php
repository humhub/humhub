<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\permissions;




/**
 * ViewAboutPage Permission
 */
class SuperAdminPermition extends \humhub\libs\BasePermission
{

        /**
     * @inheritdoc
     */
    protected $id = 'create_private_space';
    
    /**
     * @inheritdoc
     */
    protected $title = "Create private space";

    /**
     * @inheritdoc
     */
    protected $description = "Can create hidden (private) spaces.";

    /**
     * @inheritdoc
     */
    protected $moduleId = 'space';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_DENY;

}
