<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\controllers;

use humhub\modules\post\models\Post;
use Yii;

/**
 * @package humhub.modules_core.post.controllers
 * @since 0.5
 */
class PostController extends \humhub\modules\content\components\ContentContainerController
{

    public function actionPost()
    {
        // Check createPost Permission
        if (!$this->contentContainer->getPermissionManager()->can(new \humhub\modules\post\permissions\CreatePost())) {
            return [];
        }

        $post = new Post();
        $post->message = \Yii::$app->request->post('message');

        /*
          // Experimental: Auto attach found images urls in message as files
          if (isset(Yii::app()->params['attachFilesByUrlsToContent']) && Yii::app()->params['attachFilesByUrlsToContent'] == true) {
          Yii::import('application.modules_core.file.libs.*');
          RemoteFileDownloader::attachFiles($post, $post->message);
          }
         */

        return \humhub\modules\content\widgets\WallCreateContentForm::create($post, $this->contentContainer);
    }

    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');

        $edited = false;
        $model = Post::findOne(['id' => $id]);

        if (!$model->content->canEdit()) {
            $this->forbidden();
        }

        if ($model->load(Yii::$app->request->post())) {
            // Reload record to get populated updated_at field
            if ($model->validate() && $model->save()) {
                $model = Post::findOne(['id' => $id]);
                return $this->renderAjaxContent($model->getWallOut());
            } else {
                Yii::$app->response->statusCode = 400;
            }
        }

        return $this->renderAjax('edit', ['post' => $model, 'edited' => $edited]);
    }

}
