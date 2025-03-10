<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\content\models\forms\ShareIntendTargetForm;
use humhub\modules\content\services\ContentCreationService;
use Yii;

/**
 * Allows sharing files from the mobile app
 *
 * @since 1.17.2
 */
class ShareIntendController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ],
        ];
    }

    /**
     * @param $fileList array pre-uploaded File GUIDs to be attached to the new content
     * @return string
     */
    public function actionTarget()
    {
        $model = new ShareIntendTargetForm();
        $model->fileList = (array)Yii::$app->request->get('fileList');

        if ($model->load(Yii::$app->request->post())) {
            $model->validate();
        }

        return $this->renderAjax('target', [
            'model' => $model,
        ]);
    }

    /**
     * Returns a Space list by json
     *
     * It can be filtered by keyword.
     * @throws \Exception
     */
    public function actionSpaceSearchJson()
    {
        $shareService = new ContentCreationService();
        $spaces = $shareService->searchSpaces(Yii::$app->request->get('keyword'));
        return $this->asJson($spaces);
    }
}
