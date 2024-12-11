<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\permissions\ManageTopics;
use humhub\modules\user\models\User;
use humhub\widgets\ModalClose;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ManageController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['login'],
            ['permission' => ManageTopics::class],
            ['json' => ['delete']],
        ];
    }

    public function init()
    {
        parent::init();

        if ($this->contentContainer instanceof User) {
            $this->subLayout = "@humhub/modules/user/views/account/_layout";
        }
    }

    public function beforeAction($action)
    {
        if (!Topic::isAllowedToCreate($this->contentContainer)) {
            throw new ForbiddenHttpException();
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $model = new Topic($this->contentContainer);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        if ($model->hasErrors()) {
            $this->view->error($model->getFirstError('name'));
        }

        $title = $this->contentContainer instanceof User
            ? Yii::t('UserModule.account', '<strong>Account</strong> Settings')
            : Yii::t('TopicModule.base', '<strong>Topic</strong> Overview');

        return $this->render('overview', [
            'contentContainer' => $this->contentContainer,
            'dataProvider' => $this->getTopicProvider(),
            'addModel' => new Topic(),
            'title' => $title,
        ]);
    }

    private function getTopicProvider()
    {
        return new ActiveDataProvider([
            'query' => Topic::findByContainer($this->contentContainer)->orderBy('sort_order, name'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    public function actionDelete($id)
    {
        $this->forcePostRequest();

        $topic = Topic::find()
            ->where(['id' => $id, 'contentcontainer_id' => $this->contentContainer->contentcontainer_id])
            ->one();

        if (!$topic) {
            throw new NotFoundHttpException();
        }

        $topic->delete();

        return ['success' => true, 'message' => Yii::t('TopicModule.base', 'Topic has been deleted!')];
    }

    public function actionEdit($id)
    {
        $topic = Topic::find()
            ->where(['id' => $id, 'contentcontainer_id' => $this->contentContainer->contentcontainer_id])
            ->one();

        if (!$topic) {
            throw new NotFoundHttpException();
        }

        if ($topic->load(Yii::$app->request->post()) && $topic->save()) {
            return ModalClose::widget([
                'saved' => true,
                'reload' => true,
            ]);
        }

        return $this->renderAjax('editModal', [
            'model' => $topic,
        ]);
    }
}
