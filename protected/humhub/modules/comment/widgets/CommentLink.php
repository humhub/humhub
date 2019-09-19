<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\components\ActiveRecord;
use humhub\components\Widget;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use Yii;

/**
 * This widget is used to show a comment link inside the wall entry controls.
 *
 * @since 0.5
 */
class CommentLink extends Widget
{

    const MODE_INLINE = 'inline';
    const MODE_POPUP = 'popup';

    /**
     * @var ActiveRecord
     */
    public $object;

    /**
     * Mode
     *
     * inline: Show comments on the same page with CommentsWidget (default)
     * popup: Open comments popup, display only link
     *
     * @var string
     */
    public $mode;


    /**
     * @inheritDoc
     */
    public function run()
    {

        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        if ($this->mode == "") {
            $this->mode = self::MODE_INLINE;
        }

        if (!$module->canComment($this->object->content)) {
            return '';
        }

        return $this->render('link', [
            'id' => $this->object->getUniqueId(),
            'mode' => $this->mode,
            'objectModel' => $this->object->content->object_model,
            'objectId' => $this->object->content->object_id,
        ]);
    }

    /**
     * Returns count of existing comments
     *
     * @return Int the total amount of comments
     */
    public function getCommentsCount()
    {
        return CommentModel::GetCommentCount(get_class($this->object), $this->object->getPrimaryKey());
    }

}
