<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\controllers;

use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\post\models\forms\PostEditForm;
use humhub\modules\post\models\Post;
use humhub\modules\post\permissions\CreatePost;
use humhub\modules\post\widgets\Form;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * @since 0.5
 */
class PostController extends ContentContainerController
{
    /**
     * @param $id
     * @return string
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function actionView($id)
    {
        /** @var Post $post */
        $post = Post::find()
            ->contentContainer($this->contentContainer)
            ->readable()->where(['post.id' => (int)$id])->one();

        if ($post === null) {
            throw new HttpException(404);
        }

        $this->view->setPageTitle(Yii::t('PostModule.base', 'Post'), true);
        $this->view->meta->setContent($post);
        $this->view->meta->setDescription(RichTextToPlainTextConverter::process($post->message));
        $this->view->meta->setImages($post->fileManager->findAll());

        return $this->render('view', [
            'post' => $post,
            'contentContainer' => $this->contentContainer,
            'renderOptions' => new StreamEntryOptions(['viewContext' => WallStreamEntryOptions::VIEW_CONTEXT_DETAIL]),
        ]);
    }


    /**
     * @return array|mixed
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPost()
    {
        $post = new Post($this->contentContainer);

        // Check createPost Permission
        if (!$post->content->canEdit()) {
            return [];
        }

        $post->load(Yii::$app->request->post(), 'Post');

        return Post::getDb()->transaction(function ($db) use ($post) {
            return WallCreateContentForm::create($post, $this->contentContainer);
        });
    }

    public function actionEdit($id)
    {
        $post = Post::findOne(['id' => $id]);
        if (!$post) {
            throw new NotFoundHttpException();
        }

        $model = new PostEditForm(['post' => $post]);

        if (!$post->content->canEdit()) {
            $this->forbidden();
        }

        if ($model->load(Yii::$app->request->post())) {
            // Reload record to get populated updated_at field
            if ($model->save()) {
                $post = Post::findOne(['id' => $id]);
                return $this->renderAjaxContent(StreamEntryWidget::renderStreamEntry($post));
            } else {
                Yii::$app->response->statusCode = 400;
            }
        }

        return $this->renderAjax('edit', [
            'model' => $model,
            'fileHandlers' => FileHandlerCollection::getByType([FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE]),
            'submitUrl' => $post->content->container->createUrl('/post/post/edit', ['id' => $post->id]),
        ]);
    }

    public function actionCreateForm()
    {
        if (!(new Post($this->contentContainer))->content->canEdit()) {
            throw new ForbiddenHttpException();
        }

        return $this->renderAjaxPartial(Form::widget([
            'contentContainer' => $this->contentContainer,
        ]));
    }

}
