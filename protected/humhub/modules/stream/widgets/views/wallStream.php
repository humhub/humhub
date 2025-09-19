<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\stream\assets\StreamAsset;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Button;

/* @var $this View */
/* @var $filterNav string */
/* @var $contentContainer ContentContainerActiveRecord */

StreamAsset::register($this);

?>

<?php if ($contentContainer && $contentContainer->isArchived()) : ?>
    <?= Badge::warning(Yii::t('ContentModule.base', 'Archived'))
        ->left()
        ->style('margin-top:10px;') ?>
<?php endif; ?>

<!-- Stream filter section -->
<?= $filterNav ?>

<!-- Stream content -->
<?= Html::beginTag('div', $options) ?>

<!-- DIV for a normal wall stream -->
<div class="s2_stream">
    <div class="back_button_holder" style="display:none">
        <?= Button::primary(Yii::t('ContentModule.base', 'Back to stream'))->action('init')->loader(false)->sm(); ?>
    </div>
    <div class="s2_streamContent" data-stream-content></div>
</div>

<?= Html::endTag('div') ?>

<!-- show "Load More" button on mobile devices -->
<div class="col-lg-12 text-center d-lg-none">
    <?= Button::primary(Yii::t('ContentModule.base', 'Load more'))
        ->id('btn-load-more')
        ->action('loadMore', null, '#wallStream')
        ->lg() ?>
    <br/><br/>
</div>
