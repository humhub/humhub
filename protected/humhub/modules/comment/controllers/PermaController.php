<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers;

use humhub\components\Controller;
use humhub\modules\comment\models\Comment;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * PermaController provides URL to view a Comment.
 *
 * @package humhub.modules_core.comment.controllers
 * @since 1.10
 */
class PermaController extends Controller
{
    /**
     * Action to process comment permalink URL
     *
     * @param $id
     * @return \yii\console\Response|Response
     * @throws NotFoundHttpException
     */
    public function actionIndex($id)
    {
        $comment = Comment::findOne(['id' => $id]);

        if (!$comment || !$comment->content || !$comment->canView()) {
            throw new NotFoundHttpException();
        }

        $content = $comment->content;
        $record = $content->getPolymorphicRelation();

        if ($record !== null && method_exists($record, 'getCommentUrl')) {
            return $this->redirect($record->getCommentUrl($comment->id));
        }

        if ($content->container !== null) {
            return $this->redirect($content->container->createUrl(null, [
                'contentId' => $comment->content->id,
                'commentId' => $comment->id,
            ]));
        }

        if ($record !== null && method_exists($record, 'getUrl')) {
            return $this->redirect($record->getUrl());
        }

        throw new BadRequestHttpException('Content has no URL for comments');
    }

}
