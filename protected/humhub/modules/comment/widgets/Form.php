<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use Yii;
use  humhub\modules\comment\models\Comment as CommentModel;

/**
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @since 0.5
 */
class Form extends \yii\base\Widget
{

    /**
     * Content Object
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run()
    {

        if (Yii::$app->user->isGuest) {
            return '';
        }

        if (!Yii::$app->getModule('comment')->canComment($this->object->content)) {
            return '';
        }

        $objectModel = $this->object->content->object_model;
        $objectId = $this->object->content->object_id;
        if ($this->object instanceof CommentModel) {
            $objectModel = CommentModel::class;
            $objectId = $this->object->id;
        }

        return $this->render('form', [
            'modelName' => $objectModel,
            'modelId' => $objectId,
            'id' => $this->object->getUniqueId(),
        ]);
    }

}
