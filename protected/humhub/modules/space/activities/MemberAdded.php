<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\activities;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\content\models\Content;
use Yii;

/**
 * Description of MemberAdded
 *
 * @author luke
 */
class MemberAdded extends BaseActivity implements ConfigurableActivityInterface
{
    /**
     * @inheritdoc
     */
    public $viewName = 'memberAdded';

    /**
     * @inheritdoc
     */
    public $moduleId = 'space';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->visibility = Content::VISIBILITY_PRIVATE;
        parent::init();
    }

    public function getUrl()
    {
        if($this->originator) {
            return $this->originator->getUrl();
        }

        return parent::getUrl();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('SpaceModule.activities', 'Space member joined');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('SpaceModule.activities', 'Whenever a new member joined one of your spaces.');
    }

}
