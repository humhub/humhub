<?php

namespace humhub\modules\post\controllers;

use Yii;
use \humhub\modules\post\models\Post;
use yii\web\HttpException;

/**
 * @package humhub.modules_core.post.controllers
 * @since 0.5
 */
class PostController extends \humhub\components\Controller
{

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    public function actionPost()
    {
        \Yii::$app->response->format = 'json';
        $this->forcePostRequest();

        $post = new Post();
        \humhub\modules\post\widgets\Form::populateRecord($post);
        
        $post->message = \Yii::$app->request->post('message');

        if ($post->validate()) {
            $post->save();

            /*
              // Experimental: Auto attach found images urls in message as files
              if (isset(Yii::app()->params['attachFilesByUrlsToContent']) && Yii::app()->params['attachFilesByUrlsToContent'] == true) {
              Yii::import('application.modules_core.file.libs.*');
              RemoteFileDownloader::attachFiles($post, $post->message);
              }
             */

            return ['wallEntryId' => $post->content->getFirstWallEntryId()];
        } else {
            return ['errors' => $post->getErrors()];
        }
    }

    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');

        $edited = false;
        $model = Post::findOne(['id' => $id]);

        if (!$model->content->canWrite()) {
            throw new HttpException(403, Yii::t('PostModule.controllers_PostController', 'Access denied!'));
        }


        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            // Reload record to get populated updated_at field
            $model = Post::findOne(['id' => $id]);

            return $this->renderAjaxContent(\humhub\modules\post\widgets\Wall::widget(['post' => $model, 'justEdited' => true]));
        }

        return $this->renderAjax('edit', array('post' => $model, 'edited' => $edited));
    }

}
