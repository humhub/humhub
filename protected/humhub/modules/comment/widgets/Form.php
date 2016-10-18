<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use Yii;

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
            return;
        }

        if (!Yii::$app->getModule('comment')->canComment($this->object->content)) {
            return;
        }
        
        $modelName = $this->object->content->object_model;
        $modelId = $this->object->content->object_id;

        return $this->render('form', array(
                    'modelName' => $modelName,
                    'modelId' => $modelId,
                    'id' => $this->object->getUniqueId(),
        ));
    }

}

?>