<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use Yii;
use yii\base\Widget;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;

/**
 * This widget will added to the sidebar and show infos about the current selected space
 *
 * @author Andreas Strobel
 * @since 0.5
 */
class Header extends Widget
{

    /**
     * @var \humhub\modules\space\models\Space the Space which this header belongs to
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $postCount = Content::find()->where([
                    'object_model' => Post::className(),
                    'contentcontainer_id' => $this->space->contentContainerRecord->id
                ])->count();

        return $this->render('header', [
                    'space' => $this->space,
                    'followingEnabled' => !Yii::$app->getModule('space')->disableFollow,
                    'postCount' => $postCount
        ]);
    }

}

?>