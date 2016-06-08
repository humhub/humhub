<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use Yii;

/**
 * @inheritdoc
 */
class I18N extends \yii\i18n\I18N
{

    /**
     * @var string path which contains message overwrites
     */
    public $messageOverwritePath = '@config/messages';

    /**
     * @inheritdoc
     */
    public function translate($category, $message, $params, $language)
    {
        // Fix Yii source language is en-US
        if (($language == 'en' || $language == 'en_gb') && $category == 'yii') {
            $language = 'en-US';
        }
        if ($language == 'zh_cn' && $category == 'yii') {
            $language = 'zh-CN';
        }
        if ($language == 'zh_tw' && $category == 'yii') {
            $language = 'zh-TW';
        }


        return parent::translate($category, $message, $params, $language);
    }

    /**
     * @inheritdoc
     */
    public function getMessageSource($category)
    {

        // Requested MessageSource already loaded
        if (isset($this->translations[$category]) && $this->translations[$category] instanceof \yii\i18n\MessageSource) {
            return $this->translations[$category];
        }

        // Try to automatically assign Module->MessageSource
        foreach (Yii::$app->moduleManager->getModules(['includeCoreModules' => true, 'returnClass' => true]) as $moduleId => $className) {
            $moduleCategory = $this->getTranslationCategory($moduleId);
            if (substr($category, 0, strlen($moduleCategory)) === $moduleCategory) {
                $reflector = new \ReflectionClass($className);

                $this->translations[$moduleCategory . '*'] = [
                    'class' => 'humhub\components\i18n\MessageSource',
                    'sourceLanguage' => Yii::$app->sourceLanguage,
                    'sourceCategory' => $moduleCategory,
                    'basePath' => dirname($reflector->getFileName()) . '/messages',
                ];
            }
        }
        return parent::getMessageSource($category);
    }

    public function getAllowedLanguages()
    {
        $availableLanguages = Yii::$app->params['availableLanguages'];
        $allowedLanguages = Yii::$app->params['allowedLanguages'];
        if ($allowedLanguages != null && count($allowedLanguages) > 0) {
            $result = [];
            foreach ($allowedLanguages as $lang) {
                $result[$lang] = $availableLanguages[$lang];
            }
            return $result;
        }
        return $availableLanguages;
    }

    /**
     * @inheritdoc
     */
    public function format($message, $params, $language)
    {
        if (count($params) !== 0) {
            $fixedParams = [];

            // Try to fix old placeholder formats
            foreach ($params as $param => $value) {
                if (substr($param, 0, 1) === "%" && substr($param, -1, 1) === "%" && strlen($param) > 2) {
                    // Fix: %param% style params
                    $fixedParam = str_replace("%", "", $param);
                    $fixedParams[$fixedParam] = $value;
                    $message = str_replace('%' . $fixedParam . '%', '{' . $fixedParam . '}', $message);
                } elseif (substr($param, 0, 1) == "%") {
                    // Fix: %param style params
                    $fixedParam = str_replace("%", "", $param);
                    $fixedParams[$fixedParam] = $value;
                    $message = str_replace('%' . $fixedParam, '{' . $fixedParam . '}', $message);
                } elseif (substr($param, 0, 1) === "{" && substr($param, -1, 1) === "}") {
                    // Fix: {param} style params
                    $fixedParam = str_replace(['{', '}'], "", $param);
                    $fixedParams[$fixedParam] = $value;
                } elseif (substr($param, 0, 1) === ":") {
                    // Fix: :param style params
                    $fixedParam = str_replace(':', "", $param);
                    $fixedParams[$fixedParam] = $value;
                    $message = str_replace(':' . $fixedParam, '{' . $fixedParam . '}', $message);
                } else {
                    $fixedParams[$param] = $value;
                }
            }
            return parent::format($message, $fixedParams, $language);
        }

        return parent::format($message, $params, $language);
    }

    /**
     * Returns the default translation category for a given moduleId.
     * 
     * Examples:
     *      example -> ExampleModule.
     *      long_module_name -> LongModuleNameModule.
     * 
     * @param string $moduleId
     * @return strign Category Id
     */
    protected function getTranslationCategory($moduleId)
    {
        $moduleCategory = "";
        if (strpos($moduleId, '_') !== false) {
            foreach (explode("_", $moduleId) as $part) {
                $moduleCategory .= ucfirst($part);
            }
        } else {
            $moduleCategory = ucfirst($moduleId);
        }
        return $moduleCategory . "Module.";
    }

}
