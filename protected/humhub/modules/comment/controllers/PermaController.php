<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers;

use humhub\components\Controller;
use humhub\modules\comment\models\Comment;
use yii\web\NotFoundHttpException;

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
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionIndex($id)
    {
        $comment = Comment::findOne(['id' => $id]);

        if (!$comment || !$comment->canRead()) {
            throw new NotFoundHttpException();
        }

        return $this->redirect($comment->content->container->createUrl(null, [
            'contentId' => $comment->content->id,
            'commentId' => $comment->id,
        ]));
    }

}
