<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\post\models\forms\PostEditForm;
use humhub\modules\post\models\Post;
use humhub\modules\post\permissions\CreatePost;
use Yii;
use yii\helpers\StringHelper;
use yii\imagine\Image;
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
        $post = Post::find()->contentContainer($this->contentContainer)->readable()->where(['post.id' => (int)$id])->one();

        if ($post === null) {
            throw new HttpException(404);
        }

        // Add metadata to view to preview the content with Open Graph protocol
        $this->view->metaTitle = $post->getOwner()->getDisplayName() . ' · ' . $this->contentContainer->getDisplayName();
        $this->view->metaDescription = StringHelper::truncate(RichText::convert($post->message, RichText::FORMAT_SHORTTEXT), 195, '[...]');
        $previewImage = new PreviewImage();
        foreach ($post->fileManager->findAll() as $file) {
            if (
                file_exists($file->store->get())
                && $previewImage->applyFile($file) // File is an image
            ) {
                $originalImage = Image::getImagine()->open($file->store->get());
                if ($originalImage && $originalImage->getSize()->getHeight() > 200 && $originalImage->getSize()->getWidth() > 200) { // 200px is the minimum size for Facebook
                    $this->view->metaImage = $file->getUrl();
                }
                break;
            }
        }

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
        // Check createPost Permission
        if (!$this->contentContainer->getPermissionManager()->can(new CreatePost())) {
            return [];
        }

        $post = new Post($this->contentContainer);
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

}
