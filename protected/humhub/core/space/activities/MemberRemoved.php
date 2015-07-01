<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\space\activities;

use humhub\core\activity\components\BaseActivity;

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
    public function init()
    {
        $this->visibility = \humhub\core\content\models\Content::VISIBILITY_PRIVATE;
        parent::init();
    }

}
