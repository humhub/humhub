<?php

namespace humhub\modules\space\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;

/**
 * Class SpaceStatistics
 * @package humhub\modules\space\widgets
 */
class SpaceStatistic extends Widget
{

    public $space;

    public function run()
    {
        $postCount = Content::find()->where([
            'object_model' => Post::className(),
            'contentcontainer_id' => $this->space->contentContainerRecord->id
        ])->count();

        return $this->render('spaceStatistic', ['postCount' => $postCount, 'space' => $this->space]);
    }
}