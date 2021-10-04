<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\libs\Html;
use humhub\modules\comment\models\Comment;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\WidgetMenuEntry;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;
use yii\helpers\Url;

/**
 * This widget renders the controls menu for a Comment.
 *
 * @since 1.10
 */
class CommentControls extends Menu
{

    /**
     * @var Comment
     */
    public $comment;

    /**
     * @inheritdoc
     */
    public $template = '@comment/widgets/views/commentControls';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initControls();
    }

    public function initControls()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('CommentModule.base', 'Permalink'),
            'icon' => 'link',
            'url' => '#',
            'htmlOptions' => [
                'data-action-click' => 'content.permalink',
                'data-content-permalink' => Url::to(['/comment/perma', 'id' => $this->comment->id], true),
                'data-content-permalink-title' => Yii::t('CommentModule.base', '<strong>Permalink</strong> to this comment'),

            ],
            'sortOrder' => 100,
        ]));

        if ($this->comment->canEdit()) {
            $this->addEntry(new EditLink(['sortOrder' => 200, 'comment' => $this->comment]));
        }

        if ($this->comment->canDelete()) {
            $deleteUrl = Url::to(['/comment/comment/delete', 'objectModel' => $this->comment->object_model,
                'objectId' => $this->comment->object_id,
                'id' => $this->comment->id,
            ]);

            $this->addEntry(new MenuLink([
                'label' => Yii::t('CommentModule.base', 'Delete'),
                'icon' => 'delete',
                'url' => '#',
                'htmlOptions' => [
                    'data-action-click' => 'delete',
                    'data-content-delete-url' => $deleteUrl,
                ],
                'sortOrder' => 300,
            ]));
        }
    }


    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav nav-pills preferences'
        ];
    }

}
