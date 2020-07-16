<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\Module;
use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\components\Widget;

/**
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @since 0.5
 */
class Form extends Widget
{
    /**
     * @var CommentModel|ContentActiveRecord
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

        return $this->render('form', [
            'objectModel' => get_class($this->object),
            'objectId' => $this->object->getPrimaryKey(),
            'id' => $this->object->getUniqueId(),
            'isNestedComment' => ($this->object instanceof CommentModel)
        ]);
    }

}
