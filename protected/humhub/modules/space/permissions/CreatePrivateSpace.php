<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\permissions;

/**
 * CreatePrivateSpace Permission
 */
class CreatePrivateSpace extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    protected $id = 'create_private_space';
    
    /**
     * @inheritdoc
     */
    protected $title = "创建私有空间";

    /**
     * @inheritdoc
     */
    protected $description = "能够创建私有空间。";

    /**
     * @inheritdoc
     */
    protected $moduleId = 'space';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_ALLOW;

}
