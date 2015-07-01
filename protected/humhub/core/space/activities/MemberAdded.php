<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\space\activities;

use humhub\core\activity\components\BaseActivity;

/**
 * Description of MemberAdded
 *
 * @author luke
 */
class MemberAdded extends BaseActivity
{

    /**
     * @inheritdoc
     */
    public $clickable = false;

    /**
     * @inheritdoc
     */
    public $viewName = "memberAdded";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->visibility = \humhub\core\content\models\Content::VISIBILITY_PRIVATE;
        parent::init();
    }

}
