<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\components\i18n\ModuleMessageSource;
use humhub\libs\I18NHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Used to fetch translation messages from UI.
 *
 * @since 1.18
 */
class I18nController extends Controller
{
    public $access = ControllerAccess::class;

    public function actionTranslations(string $category): Response
    {
        $categories = explode(',', $category);
        $messages = [];

        foreach ($categories as $cat) {
            // A category names a message file, so it is a plain name without path segments
            if (!preg_match('/^[A-Za-z0-9_.-]+$/', $cat) || str_contains($cat, '..')) {
                throw new BadRequestHttpException('Invalid translation category.');
            }

            try {
                $messageSource = Yii::$app->i18n->getMessageSource($cat);

                if ($messageSource !== null) {
                    $originalCat = $cat;
                    if ($messageSource instanceof ModuleMessageSource) {
                        $cat = str_replace(I18NHelper::getModuleTranslationCategory($messageSource->module->id), '', $cat);
                    }

                    // Without the untranslated ones: the client shows their source message, as Yii::t() does
                    $messages[$originalCat] = array_filter(
                        $messageSource->loadMessages($cat, Yii::$app->language),
                        static fn($message) => $message !== '' && $message !== null,
                    );
                }
            } catch (\Exception) {
            }
        }

        return $this->asJson([
            'locale' => Yii::$app->language,
            'messages' => $messages,
        ]);
    }
}
