<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\widgets;

use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\post\models\Post;
use humhub\modules\post\Module;
use Yii;

/**
 * @inheritdoc
 */
class WallEntry extends WallStreamEntryWidget
{
    /**
     * Route to create a content
     *
     * @var string
     */
    public $createRoute = '/post/post/create-form';

    /**
     * @inheritdoc
     */
    public $editRoute = '/post/post/edit';

    /**
     * @inheritdoc
     */
    public $createFormSortOrder = 100;

    /**
     * @inheritdoc
     */
    public $createFormClass = Form::class;

    /**
     * @inheritdoc
     */
    protected function renderContent()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('post');

        return $this->render('wallEntry', [
            'post' => $this->model,
            'justEdited' => $this->renderOptions->isJustEdited(), // compatibility for themed legacy views
            'renderOptions' => $this->renderOptions,
            'enableDynamicFontSize' => $module->enableDynamicFontSize,
            'collapsedPostHeight' => $module->collapsedPostHeight,
        ]);
    }
}
