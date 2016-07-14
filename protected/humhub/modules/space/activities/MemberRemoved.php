<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\activities;

use humhub\modules\activity\components\BaseActivity;

/**
 * Description of MemberRemoved
 *
 * @author luke
 */
class MemberRemoved extends BaseActivity
{

    /**
     * @inheritdoc
     */
    public $clickable = false;

    /**
     * @inheritdoc
     */
    public $viewName = "memberRemoved";

    /**
     * @inheritdoc
     */
    public $moduleId = "space";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->visibility = \humhub\modules\content\models\Content::VISIBILITY_PRIVATE;
        parent::init();
    }

}
