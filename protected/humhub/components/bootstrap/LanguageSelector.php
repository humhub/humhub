<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\bootstrap;

use yii\base\BootstrapInterface;

/**
 * Description of LanguageSelector
 *
 * @author luke
 */
class LanguageSelector implements BootstrapInterface
{

    public function bootstrap($app)
    {
        mb_internal_encoding('UTF-8');
        
        $isGuest = (!$app->params['installed'] || $app->user->isGuest);

        if ($isGuest) {

            if (isset($_POST['ChooseLanguage'])) {
                /**
                 * Use language form submitted language
                 */
                $languageModel = new \humhub\models\forms\ChooseLanguage();
                if ($languageModel->load($app->request->post()) && $languageModel->validate()) {
                    $cookie = new \yii\web\Cookie([
                        'name' => 'language',
                        'value' => $languageModel->language,
                        'expire' => time() + 86400 * 365,
                    ]);
                    $app->getResponse()->getCookies()->add($cookie);
                    $app->language = $languageModel->language;
                }
            } else {
                /**
                 * Use cookie or preferred language
                 */
                $allowedLanguages = $app->i18n->getAllowedLanguages();
                $language = $app->request->getPreferredLanguage(array_keys($allowedLanguages));

                if (isset($app->request->cookies['language'])) {
                    $language = (string) $app->request->cookies['language'];

                    // Check cookie given language is available
                    if (!array_key_exists($language, $allowedLanguages)) {
                        $cookie = new \yii\web\Cookie([
                            'name' => 'language',
                            'value' => 'en',
                            'expire' => time() + 86400 * 365,
                        ]);
                        $app->getResponse()->getCookies()->add($cookie);
                        $language = 'en';
                    }
                }
                $app->language = $language;
            }
        } else {
            if ($app->user->language) {
                $app->language = $app->user->language;
            }
            if ($app->user->timeZone) {
                $app->formatter->timeZone = $app->user->timeZone;
            }
            $app->formatter->defaultTimeZone = $app->timeZone;
        }
        
        if ($app->language == 'en') {
            $app->language = 'en-US';
        }
    }

}
