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
 * CreatePublicSpace Permission
 */
class CreatePublicSpace extends BasePermission
{

    /**
     * @inheritdoc
     */
    protected $id = 'create_public_space';

    /**
     * @inheritdoc
     */
    protected $title = 'Create Public Spaces';

    /**
     * @inheritdoc
     */
    protected $description = 'Can create Spaces visible to all members.';

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
        
        $this->title = Yii::t('SpaceModule.permissions', 'Create Public Spaces');
        $this->description = Yii::t('SpaceModule.permissions', 'Can create Spaces visible to all members.');
    }

}
