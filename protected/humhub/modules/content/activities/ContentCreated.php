<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\activities;

use Yii;
use humhub\modules\activity\components\BaseActivity;

/**
 * Activity for created content 
 *
 * @see \humhub\modules\content\components\ContentActiveRecord
 * @author luke
 */
class ContentCreated extends BaseActivity
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
    public function render($mode = self::OUTPUT_WEB, $params = array())
    {
        if ($this->source === null) {
            Yii::error('Could not render ContentCreated Activity without given source - ' . $this->record->id);
            return; 
        }

        return parent::render($mode, $params);
    }

}
