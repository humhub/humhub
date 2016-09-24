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
    protected $title = 'Create private space';

    /**
     * @inheritdoc
     */
    protected $description = 'Can create hidden (private) spaces.';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'space';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_ALLOW;

    public function __construct($config = array()) {
        parent::__construct($config);
        
        $this->title = \Yii::t('SpaceModule.permissions', 'Create private space');
        $this->description = \Yii::t('SpaceModule.permissions', 'Can create hidden (private) spaces.');
    }    
}
