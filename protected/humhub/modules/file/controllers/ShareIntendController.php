<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\Controller;
use Yii;
use humhub\components\behaviors\AccessControl;
use yii\web\HttpException;

/**
 * @todo Add example how to add `shareTargets` via Events
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
        parent::init();

        $this->shareTargets[] = [
            'title' => Yii::t('FileModule.base', 'Share as Post'),
            'route' => '/post/share-intend',
        ];
    }

    public function actionIndex()
    {
        //TODO: Check if file guids are valid
        Yii::$app->session->set('shareIntendFiles', Yii::$app->request->get('fileList'));

        return $this->renderAjax('index', [
            'shareTargets' => $this->shareTargets,
            'fileList' => Yii::$app->request->get('fileList'),
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
