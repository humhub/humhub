<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use humhub\libs\I18NHelper;
use Yii;
use yii\base\InvalidArgumentException;
use humhub\models\forms\ChooseLanguage;
use yii\i18n\I18N as BaseI18N;

/**
 * I18N provides features related with internationalization (I18N) and localization (L10N).
 *
 * @inheritdoc
 */
class I18N extends BaseI18N
{
    /**
     * @var string path which contains message overwrites
     */
    public $messageOverwritePath = '@config/messages';

    /**
     * Languages which are not supported by Yii.
     * To overwrite this languages, a language file called "humhub.yii.php"
     * needs to be placed in the messages folder.
     *
     * @var array list of languages
     */
    public $unsupportedYiiLanguages = ['an'];

    /**
     * Called before the translate method is executed.
     * e.g. to modify translations on the fly.
     *
     * @since 1.4
     * @var callable
     */
    public $beforeTranslateCallback;

    /**
     * Automatically sets the current locale and time zone
     */
    public function autosetLocale()
    {
        if (!Yii::$app->isInstalled() || Yii::$app->user->isGuest) {
            $this->setGuestLocale();
        } else {
            $this->setUserLocale(Yii::$app->user->getIdentity());
        }
    }

    /**
     * Sets the current locale and time zone for a given user.
     * If no user is given the currently logged in user will be used.
     *
     * @param \humhub\modules\user\models\User $user
     */
    public function setUserLocale($user)
    {
        if ($user === null) {
            throw new InvalidArgumentException('User cannot be null!');
        }

        if (!empty($user->language)) {
            $this->setLocale($user->language);
        } else {
            $this->setDefaultLocale();
        }

        Yii::$app->formatter->timeZone = $user->time_zone;
        Yii::$app->formatter->defaultTimeZone = Yii::$app->timeZone;
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
            $this->setLocale($languageChooser->language);
        } else {
            $language = $languageChooser->getSavedLanguage();
            if ($language === null) {
                // Use browser preferred language
                $this->setLocale(Yii::$app->request->getPreferredLanguage(array_keys($this->getAllowedLanguages())));
            } else {
                $this->setLocale($language);
            }
        }
    }

    /**
     * Sets the system default locale
     */
    public function setDefaultLocale()
    {
        $this->setLocale(Yii::$app->settings->get('defaultLanguage'));
    }

    /**
     * Sets the language locale of `Yii::$app->language` and `Yii::$app->formatter`.
     *
     * @param $locale
     */
    protected function setLocale($locale)
    {
        if (!empty($locale)) {
            Yii::$app->language = $locale;
            Yii::$app->formatter->locale = $locale;
        }
    }

    /**
     * @inheritdoc
     */
    public function translate($category, $message, $params, $language)
    {
        if ($category === 'yii' && in_array($language, $this->unsupportedYiiLanguages)) {
            $category = 'humhub.yii';
        }

        if (is_callable($this->beforeTranslateCallback)) {
            list($category, $message, $params, $language)
                = $this->beforeTranslateCallback->call($this, $category, $message, $params, $language);
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
            $moduleCategory = I18NHelper::getModuleTranslationCategory($moduleId);
            if (substr($category, 0, strlen($moduleCategory)) === $moduleCategory) {
                $this->translations[$moduleCategory . '*'] = [
                    'class' => 'humhub\components\i18n\ModuleMessageSource',
                    'moduleId' => $moduleId,
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
     * Check if the provided or browser language is allowed in system, otherwise return default language
     *
     * @since 1.12.2
     * @param string|null $language NULL - to get a language from browser
     * @return string|null
     */
    public function getAllowedLanguage(?string $language = null): ?string
    {
        if (empty($language)) {
            $language = Yii::$app->language;
        }

        if (array_key_exists($language, $this->getAllowedLanguages())) {
            return $language;
        }

        return Yii::$app->settings->get('defaultLanguage');
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
}
