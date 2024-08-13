<?php
/**
 * Created by PhpStorm.
 * User: kingb
 * Date: 01.07.2018
 * Time: 14:56
 */

namespace humhub\modules\content\controllers;


use HttpException;
use humhub\libs\Html;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\forms\MoveContentForm;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Chooser;
use Yii;
use yii\data\Pagination;

class MoveController extends ContentContainerController
{
    public function actionMove($id)
    {
        $form = new MoveContentForm(['id' => $id]);

        if (!$form->content) {
            throw new HttpException(404);
        }

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            return $this->asJson([
                'success' => true,
                'id' => $id,
                'target' => $form->getTargetContainer()->id,
                'message' => Yii::t('ContentModule.base', 'Content has been moved to {spacename}', ['spacename' => Html::encode($form->getTargetContainer()->getDisplayName())])
            ]);
        }


        return $this->renderAjax('moveModal', ['model' => $form]);

    }

    /**
     * Returns a space list to move a given content to in json
     * It can be filtered by by keyword.
     */
    public function actionSearch($contentId, $keyword)
    {
        $query = Space::find()->visible()->search($keyword);

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSizeParam' => 'limit']);

        $content = Content::findOne(['id' => $contentId]);

        $json = [];
        foreach ($query->offset($pagination->offset)->limit($pagination->limit)->all() as $space) {
            $result = Chooser::getSpaceResult($space, false);

            $canMove = $content->canMove($space);
            if ($canMove !== true) {
                $result['disabled'] = true;
                $result['disabledText'] = $canMove;
            }

            $json[] = $result;
        }

        return $this->asJson($json);
    }
}
