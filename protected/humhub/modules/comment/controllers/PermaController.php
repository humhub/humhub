<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\components\ContentContainerController;
use yii\web\NotFoundHttpException;

/**
 * PermaController provides URL to view a Comment.
 *
 * @package humhub.modules_core.comment.controllers
 * @since 1.10
 */
class PermaController extends ContentContainerController
{

    /**
     * Action to process comment permalink URL
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionIndex($id)
    {
        $comment = Comment::findOne(['id' => $id]);

        if (!$comment) {
            throw new NotFoundHttpException();
        }

        return $this->redirect($comment->content->container->createUrl(null, [
            'contentId' => $comment->content->id,
            'commentId' => $comment->id,
        ]));
    }

}
