<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post;

use Yii;
use humhub\modules\post\models\Post;

/**
 * Event callbacks for the post module
 */
class Events extends \yii\base\Object
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

}
