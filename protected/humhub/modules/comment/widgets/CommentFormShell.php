<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\content\models\Content;
use Yii;
use yii\helpers\Url;

/**
 * Renders the reusable comment form SHELL (the richtext editor, mirroring
 * `comment\widgets\Form`'s former markup) once per {@see \humhub\modules\comment\widgets\Comments}
 * island - the comment module's field composition on top of the generic
 * {@see \humhub\widgets\VueFormShell} mechanism, which owns the actual `ActiveForm` shell
 * (`__VUEFORM__` token, `action`/csrf/acknowledge conventions - see that class's own docblock).
 * The client (`humhub\vue\LegacyFormWrapper.vue`, core-shared) clones the resulting HTML string
 * for every form instance it needs on the page (the main create form, an open reply form per
 * comment, an edit form) by replacing every occurrence of that token with a unique per-instance
 * id.
 *
 * No submit button and no hidden `contentId`/`parentCommentId` inputs are rendered: the island
 * owns submission (via the JSON API) and already knows both values from its own props.
 *
 * @since 1.20
 */
class CommentFormShell extends Widget
{
    public ?Content $content = null;

    public function run()
    {
        return $this->render('commentFormShell', [
            'model' => new CommentModel(),
            'mentioningUrl' => Url::to(['/user/mentioning/content', 'id' => $this->content->id]),
        ]);
    }
}
