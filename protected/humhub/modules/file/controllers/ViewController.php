<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\Controller;
use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\Response;
use humhub\components\behaviors\AccessControl;
use humhub\modules\file\models\File;
use humhub\modules\file\handler\FileHandlerCollection;

/**
 * ViewControllers provides the open modal for files
 *
 * @since 1.2
 */
class ViewController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
                'guestAllowedActions' => ['index'],
            ],
        ];
    }

    public function actionIndex()
    {
        $guid = Yii::$app->request->get('guid');
        $file = File::findOne(['guid' => $guid]);

        if (!$file) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        if (!$file->canView()) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirectToModal($file);
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
            'viewHandler' => $viewHandler,
        ]);
    }

    /**
     * The view is a modal only, so on a direct request (e.g. a copied link) it is opened
     * on top of the page of the underlying content instead
     *
     * @param File $file
     * @return Response
     */
    protected function redirectToModal(File $file)
    {
        $url = Url::home();
        $record = $file->getPolymorphicRelation();
        if ($record instanceof ContentActiveRecord && $record->content->canView()) {
            $url = $record->content->getUrl();
        }

        $modalUrl = Json::htmlEncode(Url::to(['/file/view', 'guid' => $file->guid]));
        Yii::$app->session->setFlash(
            'executeJavascript',
            'var openFileModal = function () { humhub.require("ui.modal").global.load(' . $modalUrl . '); };'
            . 'humhub.initialized ? openFileModal() : $(document).one("humhub:ready", openFileModal);',
        );

        return $this->redirect($url);
    }

}
