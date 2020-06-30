<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\Module;
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

        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        if (!$module->canComment($this->object)) {
            return '';
        }

        $objectModel = get_class($this->object);
        $objectId = $this->object->getPrimaryKey();

        return $this->render('form', [
            'modelName' => $objectModel,
            'modelId' => $objectId,
            'id' => $this->object->getUniqueId(),
        ]);
    }

}
