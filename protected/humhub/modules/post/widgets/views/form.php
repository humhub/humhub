<?php

use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\post\models\Post;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var string $mentioningUrl */
/* @var ActiveForm $form */
/* @var Post $post */
?>

<?= $form->field($post, 'message')->widget(RichTextField::class, [
    'id' => 'contentForm_message',
    'form' => $form,
    'layout' => RichTextField::LAYOUT_INLINE,
    'pluginOptions' => ['maxHeight' => '300px'],
    'placeholder' => Yii::t("PostModule.base", "What's on your mind?"),
    'name' => 'message',
    'disabled' => (property_exists(Yii::$app->controller, 'contentContainer') && Yii::$app->controller->contentContainer->isArchived()),
    'disabledText' => Yii::t("PostModule.base", "This space is archived."),
    'mentioningUrl' => $mentioningUrl,
])->label(false) ?>