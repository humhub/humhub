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
        $messageSource = Yii::$app->i18n->getMessageSource($category);

        if ($messageSource !== null) {
            if ($messageSource instanceof ModuleMessageSource) {
                $category = str_replace(I18NHelper::getModuleTranslationCategory($messageSource->module->id), '', $category);
            }

            $messages = $messageSource->loadMessages($category, Yii::$app->language);
        } else {
            $messages = [];
        }

        return $this->asJson([
            'locale' => Yii::$app->language,
            'messages' => $messages,
        ]);
    }
}
