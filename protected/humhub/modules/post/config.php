<?php

use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\richtext\AbstractRichTextEditor;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\post\Events;
use humhub\modules\post\models\Post;

return [
    'id' => 'post',
    'class' => \humhub\modules\post\Module::class,
    'isCoreModule' => true,
    'events' => [
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
        [Post::class, Post::EVENT_APPEND_RULES, [Events::class, 'onPostAppendRules']],
        [AbstractRichTextEditor::class, AbstractRichTextEditor::EVENT_INIT, [Events::class, 'onRichTextInit']]
    ]
];