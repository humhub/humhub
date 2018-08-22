<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\controllers;

use humhub\widgets\ModalClose;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\permissions\ManageTopics;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

class ManageController extends ContentContainerController
{
    public function getAccessRules()
    {
        return [
            ['permission' => ManageTopics::class],
            ['json' => ['delete']]
        ];
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

        return $this->render('overview', [
            'contentContainer' => $this->contentContainer,
            'dataProvider' => $this->getTopicProvider(),
            'addModel' => new Topic()
        ]);
    }

    private function getTopicProvider()
    {
        return new ActiveDataProvider([
            'query' =>  Topic::findByContainer($this->contentContainer)->orderBy('name'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    public function actionDelete($id)
    {
        $this->forcePostRequest();

        $topic = Topic::findOne(['id' => $id]);
        if ($topic) {
            $topic->delete();
            return ['success' => true, 'message' => Yii::t('TopicModule.base', 'Topic has been deleted!')];
        }

        return ['success' => false];
    }

    public function actionEdit($id)
    {
        $topic = Topic::findOne(['id' => $id]);

        if (!$topic) {
            throw new HttpException(404);
        }

        if ($topic->load(Yii::$app->request->post()) && $topic->save()) {
            return ModalClose::widget([
                'saved' => true,
                'reload' => true
            ]);
        }

        return $this->renderAjax('editModal', [
            'model' => $topic,
        ]);
    }
}
