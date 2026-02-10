<?php

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use humhub\modules\comment\helpers\IdHelper;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\content\models\Content;
use Yii;

class CommentLink extends Widget
{
    public const MODE_INLINE = 'inline';
    public const MODE_POPUP = 'popup';

    public Content $content;
    public ?CommentModel $parentComment = null;

    /**
     * Mode
     *
     * inline: Show comments on the same page with CommentsWidget (default)
     * popup: Open comments popup, display only link
     *
     * @var string
     */
    public $mode;


    public function run()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        if (!$module->canComment($this->content)) {
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
