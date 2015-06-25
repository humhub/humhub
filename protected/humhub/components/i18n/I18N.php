<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use Yii;

/**
 * Description of I18N
 *
 * @author luke
 */
class I18N extends \yii\i18n\I18N
{

    public function getMessageSource($category)
    {
        // Requested MessageSource already loaded
        if (isset($this->translations[$category]) && $this->translations[$category] instanceof \yii\i18n\MessageSource) {
            return $this->translations[$category];
        }

        // Try to automatically assign Module->MessageSource
        foreach (Yii::$app->getModules() as $moduleId => $config) {

            $moduleCategory = ucfirst($moduleId) . "Module.";
            if (substr($category, 0, strlen($moduleCategory)) === $moduleCategory) {

                $className = "";
                if (is_array($config) && isset($config['class'])) {
                    $className = $config['class'];
                } elseif ($config instanceof \yii\base\Module) {
                    $className = $config->className();
                }

                if ($className !== "") {
                    $reflector = new \ReflectionClass($className);

                    $this->translations[$moduleCategory . '*'] = [
                        'class' => 'humhub\components\i18n\MessageSource',
                        'sourceLanguage' => Yii::$app->sourceLanguage,
                        'sourceCategory' => $moduleCategory,
                        'basePath' => dirname($reflector->getFileName()) . '/messages',
                    ];
                }
            }
        }

        return parent::getMessageSource($category);
    }

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

}
