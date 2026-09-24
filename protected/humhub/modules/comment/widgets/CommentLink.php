<?php

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use humhub\modules\comment\helpers\IdHelper;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use Yii;

class CommentLink extends Widget
{
    public const MODE_INLINE = 'inline';
    public const MODE_POPUP = 'popup';

    public Content $content;
    public ?CommentModel $parentComment = null;

    /**
     * @deprecated since 1.20, set {@see self::$content} instead. Kept for backward
     * compatibility - some modules still call `CommentLink::widget(['object' => $x])` (the
     * API before #7917 replaced polymorphic `object` relations with `content_id`).
     * @var ContentActiveRecord|CommentModel|null
     */
    public $object = null;

    /**
     * Mode
     *
     * inline: Show comments on the same page with CommentsWidget (default)
     * popup: Open comments popup, display only link
     *
     * @var string
     */
    public $mode;

    public function init()
    {
        parent::init();

        if ($this->object !== null && !isset($this->content)) {
            if ($this->object instanceof CommentModel) {
                $this->parentComment = $this->object;
                $this->content = $this->object->content;
            } else {
                $this->content = $this->object->content;
            }
        }
    }

    public function run()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        if (Yii::$app->user->isGuest && $module->guestHideComments) {
            if ($this->parentComment !== null) {
                return '';
            }
        } elseif (!$module->canComment($this->content)) {
            return '';
        }

        if (empty($this->mode)) {
            $this->mode = self::MODE_INLINE;
        }

        return $this->render('link', [
            'id' => IdHelper::getId($this->content, $this->parentComment),
            'mode' => $this->mode,
            'content' => $this->content,
            'parentComment' => $this->parentComment,
            'commentCount' => (new CommentListService($this->content, $this->parentComment))->getCount(),
            'isNestedComment' => ($this->parentComment !== null),
            'module' => $module,
        ]);
    }
}
