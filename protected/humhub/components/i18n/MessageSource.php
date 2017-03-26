<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use Yii;

/**
 * @inheritdoc
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

    protected function getConfigMessageFilePath($category, $language)
    {
        return Yii::getAlias(Yii::$app->i18n->messageOverwritePath . "/$language/" . $category . '.php');
    }

    /**
     * @inheritdoc
     *
     * Change: Don't show warning if message file don't exists
     */
    protected function loadMessages($category, $language)
    {
        $messageFile = $this->getMessageFilePath($category, $language);
        $messages = $this->loadMessagesFromFile($messageFile);

        //Used for overwriting the default language files under @app/config/messages/.
        $configMessageFile = $this->getConfigMessageFilePath($category, $language);
        $configMessages = parent::loadMessagesFromFile($configMessageFile);

        if ($messages !== null && $configMessages !== null) {
            $messages = array_merge($messages, $configMessages);
        } elseif ($messages === null && $configMessages !== null) {
            $messages = $configMessages;
        }

        $fallbackLanguage = substr($language, 0, 2);
        if ($fallbackLanguage != $language) {
            $fallbackMessageFile = $this->getMessageFilePath($category, $fallbackLanguage);
            $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile);

            if ($messages === null && $fallbackMessages === null && $fallbackLanguage != $this->sourceLanguage) {

            } elseif (empty($messages)) {
                return $fallbackMessages;
            } elseif (!empty($fallbackMessages)) {
                foreach ($fallbackMessages as $key => $value) {
                    if (!empty($value) && empty($messages[$key])) {
                        $messages[$key] = $fallbackMessages[$key];
                    }
                }
            }
        }

        return (array) $messages;
    }

}
