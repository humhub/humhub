<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use Yii;
use yii\web\HttpException;
use humhub\components\behaviors\AccessControl;
use humhub\modules\file\models\File;
use humhub\modules\file\handler\FileHandlerCollection;

/**
 * ViewControllers provides the open modal for files
 *
 * @since 1.2
 */
class ViewController extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
                'guestAllowedActions' => ['view']
            ],
        ];
    }

    public function actionIndex()
    {
        $guid = Yii::$app->request->get('guid');
        $file = File::findOne(['guid' => $guid]);
        if ($file == null) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        $viewHandler = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_VIEW, $file);
        $exportHandler = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EXPORT, $file);

        $editHandler = [];
        $importHandler = [];
        if ($file->canDelete()) {
            $editHandler = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT, $file);
            $importHandler = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_IMPORT, $file);
        }

        return $this->renderAjax('index', [
                    'file' => $file,
                    'importHandler' => $importHandler,
                    'exportHandler' => $exportHandler,
                    'editHandler' => $editHandler,
                    'viewHandler' => $viewHandler
        ]);
    }

}
