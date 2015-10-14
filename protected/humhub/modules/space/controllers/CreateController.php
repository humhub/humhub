<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\HttpException;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\models\Setting;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\permissions\CreatePrivateSpace;

/**
 * CreateController is responsible for creation of new spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class CreateController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    public function actionIndex()
    {
        return $this->redirect(Url::to(['/space/create/create']));
    }

    /**
     * Creates a new Space
     */
    public function actionCreate()
    {
        // Use cannot create spaces (public or private)
        if (!Yii::$app->user->permissionmanager->can(new CreatePublicSpace) && !Yii::$app->user->permissionmanager->can(new CreatePrivateSpace())) {
            throw new HttpException(400, 'You are not allowed to create spaces!');
        }

        $model = new Space();
        $model->scenario = 'create';
        $model->visibility = Setting::Get('defaultVisibility', 'space');
        $model->join_policy = Setting::Get('defaultJoinPolicy', 'space');

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->getSession()->setFlash('ws', 'created');
            return $this->htmlRedirect($model->getUrl());
        }

        return $this->renderAjax('create', array('model' => $model));
    }

}

?>
