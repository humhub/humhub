<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\topic\widgets\TopicPicker;

class TopicController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public $requireContainer = false;

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['json'],
        ];
    }

    public function actionSearch($keyword)
    {
        return $this->contentContainer
            ? TopicPicker::searchByContainer($keyword, $this->contentContainer)
            : TopicPicker::search($keyword);
    }
}
