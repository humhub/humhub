<?php

use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\content\widgets\WallCreateContentFormFooter;
use humhub\modules\post\models\Post;
use humhub\widgets\form\ActiveForm;

/* @var WallCreateContentForm $wallCreateContentForm */
/* @var string $mentioningUrl */
/* @var ActiveForm $form */
/* @var Post $post */
?>

<?= $form->field($post, 'message', ['template' => "{input}"])->widget(RichTextField::class, [
    'id' => 'contentForm_message' . ($wallCreateContentForm->isModal ? 'Modal' : ''),
    'form' => $form,
    'layout' => $wallCreateContentForm->isModal ? RichTextField::LAYOUT_BLOCK : RichTextField::LAYOUT_INLINE,
    'pluginOptions' => ['maxHeight' => '300px'],
    'placeholder' => Yii::t("PostModule.base", "What's on your mind?"),
    'name' => 'message',
    'disabled' => (property_exists(Yii::$app->controller, 'contentContainer') && Yii::$app->controller->contentContainer->isArchived()),
    'disabledText' => Yii::t("PostModule.base", "This space is archived."),
    'mentioningUrl' => $mentioningUrl,
]) ?>

<?= WallCreateContentFormFooter::widget([
    'contentContainer' => $post->content->container,
    'wallCreateContentForm' => $wallCreateContentForm,
]) ?>
