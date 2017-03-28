<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use Yii;
use humhub\models\forms\ChooseLanguage;

/**
 * I18N provides features related with internationalization (I18N) and localization (L10N).
 *
 * @inheritdoc
 */
class I18N extends \yii\i18n\I18N
{

    /**
     * @var string path which contains message overwrites
     */
    public $messageOverwritePath = '@config/messages';

    /**
     * Automatically sets the current locale
     */
    public function autosetLocale()
    {
        if (!Yii::$app->params['installed'] || Yii::$app->user->isGuest) {
            $this->setGuestLocale();
        } else {
            $this->setUserLocale(Yii::$app->user->getIdentity());
        }
    }

    /**
     * Sets the current locale for a given user.
     * If no user is given the currently logged in user will be used.
     *
     * @param \humhub\modules\user\models\User $user
     */
    public function setUserLocale($user)
    {
        if ($user === null) {
            throw \yii\base\InvalidParamException("User cannot be null!");
        }

        if (!empty($user->language)) {
            Yii::$app->language = $user->language;
        }

        if (!($user->time_zone)) {
            Yii::$app->formatter->timeZone = $user->time_zone;
        }
        Yii::$app->formatter->defaultTimeZone = Yii::$app->timeZone;

        $this->fixLocaleCodes();
    }

    /**
     * Sets the locale for the current guest user.
     *
     * The language is determined by the a cookie
     */
    public function setGuestLocale()
    {
        if (is_a(Yii::$app, 'yii\console\Application')) {
            $this->setDefaultLocale();
            return;
        }

        $languageChooser = new ChooseLanguage();
        if ($languageChooser->load(Yii::$app->request->post()) && $languageChooser->save()) {
            Yii::$app->language = $languageChooser->language;
        } else {
            $language = $languageChooser->getSavedLanguage();
            if ($language === null) {
                // Use browser preferred language
                $language = Yii::$app->request->getPreferredLanguage(array_keys($this->getAllowedLanguages()));
            }
            Yii::$app->language = $language;
        }

        $this->fixLocaleCodes();
    }

    /**
     * Sets the system default locale
     */
    public function setDefaultLocale()
    {
        Yii::$app->language = Yii::$app->settings->get('defaultLanguage');

        $this->fixLocaleCodes();
    }

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

        if ($language == 'nb_no' && $category == 'yii') {
            $language = 'nb-NO';
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

    /**
     * Returns an array of allowed/available language codes
     *
     * @return array the allowed languages
     */
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
     * @return string Category Id
     */
    protected function getTranslationCategory($moduleId)
    {
        return implode('', array_map("ucfirst", preg_split("/(_|\-)/", $moduleId))) . 'Module.';
    }

    /**
     * Ensure that old language codes are translated to the
     * current format.
     */
    protected function fixLocaleCodes()
    {
        if (Yii::$app->language == 'en') {
            Yii::$app->language = 'en-US';
        }
    }

}
