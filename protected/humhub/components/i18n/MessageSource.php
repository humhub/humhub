<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use Yii;

/**
 * Description of MessageSource
 *
 * @author luke
 */
class MessageSource extends \yii\i18n\PhpMessageSource
{

    public $sourceCategory = '';

    protected function getMessageFilePath($category, $language)
    {
        $category = str_replace($this->sourceCategory, '', $category);
        return parent::getMessageFilePath($category, $language);
    }

    protected function loadMessagesFromFile($messageFile)
    {
        $messageFile = str_replace($this->sourceCategory, '', $messageFile);
        return parent::loadMessagesFromFile($messageFile);
    }

}
