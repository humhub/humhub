<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\controllers;


use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\models\Content;
use humhub\modules\topic\models\forms\ContentTopicsForm;
use humhub\modules\topic\widgets\TopicPicker;
use Yii;
use yii\web\HttpException;

class TopicController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['json']
        ];
    }

    public function actionSearch($keyword)
    {
        return TopicPicker::searchByContainer($keyword, $this->contentContainer);
    }
}
