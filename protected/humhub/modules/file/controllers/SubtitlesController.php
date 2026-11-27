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
use humhub\modules\file\libs\FileVariants;
use humhub\modules\file\models\File;
use humhub\modules\file\serializers\FileSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\JsonParser;
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

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // The manager island posts JSON
        Yii::$app->request->parsers['application/json'] = JsonParser::class;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST'], 'delete' => ['POST']],
            ],
        ]);
    }

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
     * Stores the subtitles or captions of one language, the JSON body is `{"name": "subtitles-de.vtt", "content": "…"}`
     * (the name tells the kind, `captions-de.vtt`); `201` for a new variant, `200` for a replaced one with the
     * variant as the variants API lists it, `422 {"errors": {field: [messages]}}` for an invalid one.
     */
    public function actionSave(string $guid = '')
    {
        $file = $this->findEditableMedia($guid);
        $variants = new FileVariants($file);
        $request = Yii::$app->request;

        $name = $request->getBodyParam('name');
        $content = $request->getBodyParam('content');

        if (!is_string($name)) {
            $errors = ['name' => [Yii::t('FileModule.base', 'A variant of this name can\'t be written.')]];
        } elseif (!is_string($content)) {
            $errors = ['content' => [Yii::t('FileModule.base', 'The text is required.')]];
        } else {
            $existed = $variants->has($name);
            $errors = $variants->put($name, $content);
        }

        if ($errors !== []) {
            Yii::$app->response->setStatusCode(422);

            return $this->asJson(['errors' => $errors]);
        }

        Yii::$app->response->setStatusCode($existed ? 200 : 201);

        return $this->asJson(FileSerializer::variant($file, $variants->get($name)));
    }

    /**
     * Deletes the subtitles or captions `{"name": …}`: `200 {}`, `404` when there are none, `422` for a name that
     * is no subtitles'
     */
    public function actionDelete(string $guid = '')
    {
        $file = $this->findEditableMedia($guid);
        $name = Yii::$app->request->getBodyParam('name');

        if (!is_string($name) || !FileVariants::isWritable($name)) {
            Yii::$app->response->setStatusCode(422);

            return $this->asJson(['errors' => ['name' => [Yii::t('FileModule.base', 'A variant of this name can\'t be written.')]]]);
        }

        if (!(new FileVariants($file))->delete($name)) {
            throw new NotFoundHttpException();
        }

        return $this->asJson([]);
    }

    /**
     * @throws NotFoundHttpException when there is no such media file
     * @throws ForbiddenHttpException when the user may not edit the file
     */
    private function findEditableMedia(string $guid): File
    {
        $file = File::findOne(['guid' => $guid]);

        if ($file === null || !$this->isMedia($file)) {
            throw new NotFoundHttpException(Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        if (!$file->canDelete()) {
            throw new ForbiddenHttpException(Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        return $file;
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
