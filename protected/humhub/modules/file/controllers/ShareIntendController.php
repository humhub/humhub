<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\file\models\File;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Modules can be added as an additional target by
 * registering an event on `ShareIntendController::EVENT_INIT`
 * and adding the following method to the Events class:
 *
 * ```
 * public static function onShareIntendControllerInit($event)
 * {
 *     $event->sender->shareTargets[] = [
 *         'title' => 'Your Module',
 *         'route' => '/your-module/share-intend/index',
 *     ];
 * }
 * ```
 *
 * The module must have the ShareIntendController and view,
 * similar to the Post module.
 * The controller must extend \humhub\modules\content\controllers\ShareIntendController
 */
final class ShareIntendController extends Controller
{
    public $shareTargets = [];

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ],
        ];
    }

    public function init()
    {
        $this->shareTargets[] = [
            'title' => Yii::t('FileModule.base', 'Share as Post'),
            'route' => '/post/share-intend',
        ];

        parent::init();
    }

    public function actionIndex()
    {
        $fileList = Yii::$app->request->get('fileList');
        if (!$fileList) {
            throw new NotFoundHttpException('No files to share found!');
        }

        // Check if the files exists, and if user is the owner of the files
        if (File::find()->where(['guid' => $fileList])->andWhere(['created_by' => Yii::$app->user->id])->count() !== count($fileList)) {
            throw new NotFoundHttpException('Files not uploaded correctly!');
        }

        if (count($this->shareTargets) === 0) {
            throw new NotFoundHttpException('No sharing targets found!');
        }

        Yii::$app->session->set('shareIntendFiles', $fileList);

        return $this->renderAjax('index', [
            'shareTargets' => $this->shareTargets,
        ]);
    }

    public static function checkShareFileGuids(): void
    {
        $fileGuids = Yii::$app->session->get('shareIntendFiles');

        if (empty($fileGuids)) {
            throw new HttpException('500', 'No files to share found!');
        }
    }

    public static function getShareFileGuids(): ?array
    {
        return Yii::$app->session->get('shareIntendFiles');
    }
}
