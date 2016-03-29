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
    protected $title = 'Create public space';

    /**
     * @inheritdoc
     */
    protected $description = 'Can create public visible spaces. (Listed in directory)';

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
        
        $this->title = \Yii::t('SpaceModule.permissions', 'Create public space');
        $this->description = \Yii::t('SpaceModule.permissions', 'Can create public visible spaces. (Listed in directory)');
    }

}
