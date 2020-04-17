<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\controllers;

use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\post\models\Post;
use humhub\modules\post\permissions\CreatePost;
use humhub\modules\stream\actions\Stream;
use Yii;

/**
 * @package humhub.modules_core.post.controllers
 * @since 0.5
 */
class PostController extends ContentContainerController
{

    /**
     * @return array|mixed
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPost()
    {
        // Check createPost Permission
        if (!$this->contentContainer->getPermissionManager()->can(new CreatePost())) {
            return [];
        }

        $post = new Post($this->contentContainer);
        $post->message = Yii::$app->request->post('message');

        return Post::getDb()->transaction(function($db) use($post) {
            return WallCreateContentForm::create($post, $this->contentContainer);
        });
    }

    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');
        $from = Yii::$app->request->get('from', '');

        $model = Post::findOne(['id' => $id]);

        if (!$model->content->canEdit()) {
            $this->forbidden();
        }

        if ($model->load(Yii::$app->request->post())) {
            // Reload record to get populated updated_at field
            if ($model->validate() && $model->save()) {
                $model = Post::findOne(['id' => $id]);
                $options = [];
                if ($from === Stream::FROM_DASHBOARD) {
                    $options['controlsOptions'] = [
                        'showContentContainer' => true
                    ];
                }
                return $this->renderAjaxContent($model->getWallOut($options));
            } else {
                Yii::$app->response->statusCode = 400;
            }
        }

        return $this->renderAjax('edit', [
            'post' => $model,
            'from' => $from === Stream::FROM_DASHBOARD ? $from : null
        ]);
    }

}
