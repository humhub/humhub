<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\activities;

use Yii;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

/**
 * Activity for created content 
 *
 * @see \humhub\modules\content\components\ContentActiveRecord
 * @author luke
 */
class ContentCreated extends BaseActivity implements ConfigurableActivityInterface
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'content';

    /**
     * @inheritdoc
     */
    public $viewName = 'created';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('ContentModule.activities', 'Contents');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('ContentModule.activities', 'Whenever a new content (e.g. post) has been created.');
    }

}
