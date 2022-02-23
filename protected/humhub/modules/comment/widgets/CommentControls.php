<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment;
use humhub\modules\ui\menu\MenuLink;
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
                'data-content-permalink' => $this->comment->url,
                'data-content-permalink-title' => Yii::t('CommentModule.base', '<strong>Permalink</strong> to this comment'),

            ],
            'sortOrder' => 100,
        ]));

        if ($this->comment->canEdit()) {
            $this->addEntry(new EditLink(['sortOrder' => 200, 'comment' => $this->comment]));
        }

        if ($this->comment->canDelete()) {
            $isAdmin = $this->comment->created_by !== Yii::$app->user->id;

            $deleteUrl = Url::to(['/comment/comment/delete',
                'objectModel' => $this->comment->object_model,
                'objectId' => $this->comment->object_id,
                'id' => $this->comment->id,
            ]);

            if($isAdmin) {
                $adminDeleteModalUrl = Url::to(['/comment/comment/get-admin-delete-modal',
                    'objectModel' => $this->comment->object_model,
                    'objectId' => $this->comment->object_id,
                    'id' => $this->comment->id,
                ]);
            }

            $htmlOptions = [
                'data-action-click' => $isAdmin ? 'adminDelete' : 'delete',
                'data-content-delete-url' => $deleteUrl
            ];

            if($isAdmin) {
                $htmlOptions['data-admin-delete-modal-url'] = $adminDeleteModalUrl;
            }

            $this->addEntry(new MenuLink([
                'label' => Yii::t('CommentModule.base', 'Delete'),
                'icon' => 'delete',
                'url' => '#',
                'htmlOptions' => $htmlOptions,
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
