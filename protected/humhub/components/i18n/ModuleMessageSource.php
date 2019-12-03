<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use humhub\components\Module;
use humhub\libs\I18NHelper;
use Yii;
use yii\base\InvalidConfigException;


/**
 * ModuleMessageSource
 *
 * @since 1.4
 * @package humhub\components\i18n
 */
class ModuleMessageSource extends PhpMessageSource
{
    /**
     * @var string the id of the module
     */
    public $moduleId;

    /**
     * @var Module the module
     */
    public $module;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function init()
    {
        if ($this->module === null && !empty($this->moduleId)) {
            $this->module = Yii::$app->moduleManager->getModule($this->moduleId);
        }

        if ($this->module === null) {
            throw new InvalidConfigException("Module cannot be null!");
        }

        $this->basePath = $this->module->getBasePath() . '/messages';
    }

    /**
     * @inheritDoc
     */
    public function translate($category, $message, $language)
    {
        $category = str_replace(I18NHelper::getModuleTranslationCategory($this->module->id), '', $category);
        return parent::translate($category, $message, $language);
    }

    /**
     * @inheritDoc
     */
    protected function loadMessages($category, $language)
    {
        $messages = parent::loadMessages($category, $language);

        // Merge message overwrites specified in configuration folder
        $configMessages = parent::loadMessagesFromFile($this->getConfigMessageFilePath($category, $language));
        if ($configMessages !== null) {
            $messages = array_merge($messages, $configMessages);
        }

        return $messages;
    }

    /**
     * Returns the message file for messages overrides via configuration
     *
     * @param $category
     * @param $language
     * @return string
     */
    private function getConfigMessageFilePath($category, $language)
    {
        return Yii::getAlias(Yii::$app->i18n->messageOverwritePath . "/$language/" . I18NHelper::getModuleTranslationCategory($this->module->id) . $category . '.php');
    }

}
