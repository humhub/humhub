<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use Yii;
use yii\i18n\PhpMessageSource as PhpMessageSourceYii;

/**
 * @inheritDoc
 */
class PhpMessageSource extends PhpMessageSourceYii
{
    /**
     * @inheritDoc
     */
    protected function getMessageFilePath($category, $language)
    {
        $messageFile = Yii::getAlias($this->basePath) . '/' . $language . '/';

        // Try old language code syntax (e.g. pt_br instead pt-BR)
        if (!is_dir($messageFile) && strpos($language, '-') !== false) {
            $language = strtolower(str_replace('-', '_', $language));
            if (is_dir(Yii::getAlias($this->basePath) . '/' . $language . '/')) {
                $messageFile = Yii::getAlias($this->basePath) . '/' . $language . '/';
            }
        }

        if (isset($this->fileMap[$category])) {
            $messageFile .= $this->fileMap[$category];
        } else {
            $messageFile .= str_replace('\\', '/', $category) . '.php';
        }

        return $messageFile;
    }

    /**
     * @inheritDoc
     *
     * Actually the original method with reduced error messages!
     */
    protected function loadMessages($category, $language)
    {
        $messageFile = $this->getMessageFilePath($category, $language);
        $messages = $this->loadMessagesFromFile($messageFile);

        $fallbackLanguage = substr($language, 0, 2);
        $fallbackSourceLanguage = is_string($this->sourceLanguage) ? substr($this->sourceLanguage, 0, 2) : null;

        if ($language !== $fallbackLanguage) {
            $messages = $this->loadFallbackMessages($category, $fallbackLanguage, $messages, $messageFile);
        } elseif ($language === $fallbackSourceLanguage) {
            $messages = $this->loadFallbackMessages($category, $this->sourceLanguage, $messages, $messageFile);
        } else {
            if ($messages === null) {
                // modification warning --> debug
                Yii::debug("The message file for category '$category' does not exist: $messageFile", __METHOD__);
            }
        }

        return (array)$messages;
    }

    /**
     * @inheritDoc
     *
     * Actually the original method with reduced error messages!
     */
    protected function loadFallbackMessages($category, $fallbackLanguage, $messages, $originalMessageFile)
    {
        $fallbackMessageFile = $this->getMessageFilePath($category, $fallbackLanguage);
        $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile);
        $fallbackSourceLanguage = is_string($this->sourceLanguage) ? substr($this->sourceLanguage, 0, 2) : null;

        if (
            $messages === null && $fallbackMessages === null
            && $fallbackLanguage !== $this->sourceLanguage
            && $fallbackLanguage !== $fallbackSourceLanguage
        ) {
            // modification warning --> debug
            Yii::debug("The message file for category '$category' does not exist: $originalMessageFile "
                . "Fallback file does not exist as well: $fallbackMessageFile", __METHOD__);
        } elseif (empty($messages)) {
            return $fallbackMessages;
        } elseif (!empty($fallbackMessages)) {
            foreach ($fallbackMessages as $key => $value) {
                if (!empty($value) && empty($messages[$key])) {
                    $messages[$key] = $fallbackMessages[$key];
                }
            }
        }

        return (array)$messages;
    }


}
