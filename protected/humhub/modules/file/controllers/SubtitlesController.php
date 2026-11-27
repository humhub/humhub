<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\Controller;
use humhub\modules\file\components\RedirectsToFileModal;
use Collator;
use humhub\modules\file\models\File;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The modal managing the subtitles of an audio or video file (see
 * {@see \humhub\modules\file\handler\SubtitlesFileHandler})
 *
 * @since 1.20
 */
class SubtitlesController extends Controller
{
    use RedirectsToFileModal;

    public function actionIndex(string $guid = '')
    {
        $file = File::findOne(['guid' => $guid]);

        if ($file === null || !$this->isMedia($file)) {
            throw new NotFoundHttpException(Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        if (!$file->canDelete()) {
            throw new ForbiddenHttpException(Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirectToFileModal($file, ['/file/subtitles', 'guid' => $file->guid]);
        }

        return $this->renderAjax('index', [
            'file' => $file,
            'languages' => $this->getLanguages(),
            'uiLanguage' => $this->getUiLanguage(),
        ]);
    }

    /**
     * The platform's languages as the language field of the appearance settings lists them (names sorted in the
     * UI language)
     *
     * @return array<int, array{code: string, name: string}>
     */
    private function getLanguages(): array
    {
        $names = Yii::$app->i18n->getAllowedLanguages();
        (new Collator(Yii::$app->language))->asort($names);

        $languages = [];
        foreach ($names as $code => $name) {
            $languages[] = ['code' => (string)$code, 'name' => (string)$name];
        }

        return $languages;
    }

    /**
     * The code of the platform language that is the current UI language: the exact one, else the first with the
     * same primary language (`en` for `en-US`); an empty string if there is none
     */
    private function getUiLanguage(): string
    {
        $language = strtolower(str_replace('_', '-', Yii::$app->language));
        $primary = strtok($language, '-');
        $match = '';

        foreach (array_keys(Yii::$app->i18n->getAllowedLanguages()) as $code) {
            $code = (string)$code;
            if (strtolower($code) === $language) {
                return $code;
            }
            if ($match === '' && strtolower(strtok($code, '-')) === $primary) {
                $match = $code;
            }
        }

        return $match;
    }

    private function isMedia(File $file): bool
    {
        return str_starts_with((string)$file->mime_type, 'video/') || str_starts_with((string)$file->mime_type, 'audio/');
    }
}
