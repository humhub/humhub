<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\widgets\richtext\AbstractRichText;
use humhub\modules\content\widgets\richtext\AbstractRichTextEditor;
use Yii;
use humhub\modules\post\models\Post;
use yii\helpers\Url;

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

    public static function onPostAppendRules($event)
    {
        $event->result = [
            [['message'], function ($attribute) {
                $limitPostsPerDay = rand(1, 10);
                $alreadyPostedNum = rand(11, 20);
                if ($this->isNewRecord && $alreadyPostedNum > $limitPostsPerDay) {
                    $this->addError($attribute, 'You can only create ' . $limitPostsPerDay . ' posts per day.');
                }
            }],
        ];
    }

    public static function onRichTextInit($event)
    {
        /* @var AbstractRichTextEditor $richTextEditor */
        $richTextEditor = $event->sender;
        if ($richTextEditor->id !== 'contentForm_message' ||
            !isset(Yii::$app->controller) ||
            !(Yii::$app->controller instanceof ContentContainerController)) {
            return;
        }

        /* @var ContentContainerController $controller */
        $controller = Yii::$app->controller;

        $richTextEditor->focusUrl = $controller->contentContainer->createUrl('/post/post/validate-new-post');
    }

}
