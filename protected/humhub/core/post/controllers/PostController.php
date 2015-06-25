<?php

namespace humhub\core\post\controllers;

use \humhub\core\post\models\Post;

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
        $post->content->populateByForm();
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
        $id = Yii::app()->request->getParam('id');

        $edited = false;
        $model = Post::model()->findByPk($id);

        if ($model->content->canWrite()) {

            if (isset($_POST['Post'])) {
                $_POST['Post'] = Yii::app()->input->stripClean($_POST['Post']);
                $model->attributes = $_POST['Post'];
                if ($model->validate()) {
                    $model->save();

                    // Reload record to get populated updated_at field
                    $model = Post::model()->findByPk($id);

                    // Return the new post
                    $output = $this->widget('application.modules_core.post.widgets.PostWidget', array('post' => $model, 'justEdited' => true), true);
                    Yii::app()->clientScript->render($output);
                    echo $output;
                    return;
                }
            }

            $this->renderPartial('edit', array('post' => $model, 'edited' => $edited), false, true);
        } else {
            throw new CHttpException(403, Yii::t('PostModule.controllers_PostController', 'Access denied!'));
        }
    }

}
