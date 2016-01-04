<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\permissions;

/**
 * CreatePublicSpace Permission
 */
class CreatePublicSpace extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    protected $id = 'create_public_space';

    /**
     * @inheritdoc
     */
    protected $title = "创建公共空间";

    /**
     * @inheritdoc
     */
    protected $description = "能创建公共可见的空间。";

    /**
     * @inheritdoc
     */
    protected $moduleId = 'space';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_ALLOW;

}
