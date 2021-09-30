<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment;
use yii\base\Widget;

/**
 * CommentControl for Comment
 *
 * @since 1.10
 */
class CommentControl extends Widget
{

    /**
     * @var Comment $comment
     */
    public $comment;

    /**
     * Check the comment control link is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function render($view, $params = [])
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return parent::render($view, $params);
    }

}
