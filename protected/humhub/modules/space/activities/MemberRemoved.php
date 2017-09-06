<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\activities;

use Yii;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

/**
 * Description of MemberRemoved
 *
 * @author luke
 */
class MemberRemoved extends BaseActivity implements ConfigurableActivityInterface
{

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

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('SpaceModule.activities', 'Space member left');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('SpaceModule.activities', 'Whenever a member leaves one of your spaces.');
    }

}
