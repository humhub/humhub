<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post;

use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\content\widgets\WallCreateContentMenu;
use humhub\modules\post\models\Post;
use humhub\modules\post\permissions\CreatePost;
use humhub\modules\ui\menu\MenuLink;
use Yii;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;

/**
 * Event callbacks for the post module
 */
class Events extends \yii\base\BaseObject
{

    /**
     * Callback to validate module database records.
     *
     * @param \yii\base\Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;

        $integrityController->showTestHeadline("Post  Module - Posts (" . Post::find()->count() . " entries)");
        foreach (Post::find()->all() as $post) {
            if (empty($post->content->id)) {
                if ($integrityController->showFix("Deleting post " . $post->id . " without existing content record!")) {
                    $post->delete();
                }
            }
        }
    }

    public static function onInitWallCreateContentMenu($event)
    {
        /* @var WallCreateContentMenu $menu */
        $menu = $event->sender;

        if ($menu->contentContainer && $menu->contentContainer->getPermissionManager()->can(CreatePost::class)) {
            $menu->addEntry(new MenuLink([
                'label' => Yii::t('PostModule.base', 'Post'),
                'url' => '#',
                'sortOrder' => 100,
                'isActive' => true,
                'htmlOptions' => [
                    'data-action-click' => 'loadForm',
                    'data-action-url' => $menu->contentContainer->createUrl('/post/post/form'),
                ],
            ]));
        }
    }

}
