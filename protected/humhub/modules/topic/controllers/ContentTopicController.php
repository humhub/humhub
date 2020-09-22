<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\controllers;

use humhub\widgets\ModalClose;
use humhub\components\Controller;
use humhub\modules\content\models\Content;
use humhub\modules\topic\models\forms\ContentTopicsForm;
use Yii;
use yii\web\HttpException;

class ContentTopicController extends Controller
{
    public function actionIndex($contentId)
    {
        $content = Content::findOne(['id' => $contentId]);

        if (!$content) {
            throw new HttpException(404);
        } elseif (!$content->canEdit()) {
            throw new HttpException(403);
        }

        $form = new ContentTopicsForm(['content' => $content]);

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            return ModalClose::widget(['script' => 'humhub.modules.stream.getEntry('.$content->id.').reload()']);
        }

        return $this->renderAjax('edit', ['model' => $form]);
    }
}
