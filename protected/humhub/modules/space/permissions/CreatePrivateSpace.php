<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\permissions;

use humhub\libs\BasePermission;
use Yii;

/**
 * CreatePrivateSpace Permission
 */
class CreatePrivateSpace extends BasePermission
{

    /**
     * @inheritdoc
     */
    protected $id = 'create_private_space';
    
    /**
     * @inheritdoc
     */
    protected $title = 'Create Private Spaces';

    /**
     * @inheritdoc
     */
    protected $description = 'Can create hidden (private) Spaces.';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'space';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_ALLOW;

    public function __construct($config = []) {
        parent::__construct($config);
        
        $this->title = Yii::t('SpaceModule.permissions', 'Create Private Spaces');
        $this->description = Yii::t('SpaceModule.permissions', 'Can create hidden (private) Spaces.');
    }    
}
