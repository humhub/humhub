<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use Yii;
use humhub\components\Controller;
use humhub\components\behaviors\AccessControl;
use humhub\widgets\MarkdownView;

/**
 * MarkdownController provides preview for MarkdownEditorWidget
 *
 * @author luke
 * @since 0.11
 */
class MarkdownController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
            ]
        ];
    }

    public function actionPreview()
    {
        $this->forcePostRequest();

        return MarkdownView::widget(['markdown' => Yii::$app->request->post('markdown')]);
    }
}
