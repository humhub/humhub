<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\activities;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\content\models\Content;
use Yii;

/**
 * Activity when somebody follows an object
 *
 * @author luke
 */
class UserFollow extends BaseActivity implements ConfigurableActivityInterface
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'user';

    /**
     * @inheritdoc
     */
    public $viewName = "userFollow";

    /**
     * @inheritdoc
     */
    public $visibility = Content::VISIBILITY_PUBLIC;

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->source->target->getUrl();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('UserModule.base', 'Following (User)');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('UserModule.base', 'Whenever a user follows another user.');
    }
}
