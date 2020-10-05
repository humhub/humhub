<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\widgets;

use humhub\modules\content\widgets\stream\WallStreamEntryWidget;

/**
 * @inheritdoc
 */
class WallEntry extends WallStreamEntryWidget
{

    /**
     * @inheritdoc
     */
    public $editRoute = '/post/post/edit';

    /**
     * @inheritdoc
     */
    protected function renderContent()
    {
        return $this->render('wallEntry', [
            'post' => $this->model,
            'justEdited' => $this->renderOptions->isJustEdited(), // compatibility for themed legacy views
            'renderOptions' => $this->renderOptions
        ]);
    }
}
