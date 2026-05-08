<?php

use humhub\helpers\Html;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $file \humhub\modules\file\models\File */
/* @var $viewHandler BaseFileHandler[] */
/* @var $editHandler BaseFileHandler[] */
/* @var $exportHandler BaseFileHandler[] */
/* @var $importHandler BaseFileHandler[] */
?>

<?php Modal::beginDialog([
    'title' => Yii::t('FileModule.base', '<strong>Open</strong> file', ['fileName' => Html::encode($file->file_name)]),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>

    <?php
    $thumbnailUrl = '';
    $previewImage = new PreviewImage();
    if ($previewImage->applyFile($file)) {
        $thumbnailUrl = $previewImage->getUrl();
    }
    ?>

    <img src="<?= $thumbnailUrl; ?>" class="float-start" style="padding-right:12px">

    <h3 style="padding-top:0px;margin-top:0px"><?= Html::encode($file->file_name); ?></h3>
    <br/>

    <p style="line-height:20px">
        <strong><?= Yii::t('FileModule.base', 'Size:'); ?></strong> <?= Yii::$app->formatter->asShortSize($file->size, 1); ?>
        <br/>
        <strong><?= Yii::t('FileModule.base', 'Created by:'); ?></strong> <?= Html::encode($file->createdBy->displayName); ?>
        (<?= Yii::$app->formatter->asDatetime($file->created_at, 'short'); ?>)<br/>
        <?php if (!empty($file->updatedBy) && $file->updated_at != $file->created_at) : ?>
            <strong><?= Yii::t('FileModule.base', 'Last update by:') ?></strong> <?= Html::encode($file->updatedBy->displayName); ?> (<?= Yii::$app->formatter->asDatetime($file->updated_at, 'short'); ?>)
            <br/>
        <?php endif; ?>
    </p>

    <div class="float-start">
        <?php
        /**
         * Renders a group of file handler buttons (view, export, edit/import).
         * @param BaseFileHandler[] $handlers
         * @param string $cssButtonClass
         */
        $renderHandlerButtons = static function (array $handlers, string $cssButtonClass = 'btn-default') use (&$renderHandlerButtons): string {
            if (empty($handlers)) {
                return '';
            }
            $output = Html::beginTag('div', ['class' => 'btn-group']);
            $firstAttrs = array_shift($handlers)->getLinkAttributes();
            $firstAttrs['data-action-process'] = 'file-handler';
            Html::addCssClass($firstAttrs, ['btn', $cssButtonClass]);
            $label = \yii\helpers\ArrayHelper::remove($firstAttrs, 'label', '');
            if (isset($firstAttrs['url'])) {
                $firstAttrs['href'] = \yii\helpers\ArrayHelper::remove($firstAttrs, 'url', '#');
            }
            $output .= Html::tag('a', $label, $firstAttrs);
            if (!empty($handlers)) {
                $output .= '<button type="button" class="btn ' . $cssButtonClass . ' btn-icon-only dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>';
                $output .= Html::beginTag('ul', ['class' => 'dropdown-menu']);
                foreach ($handlers as $handler) {
                    $attrs = $handler->getLinkAttributes();
                    $attrs['data-action-process'] = 'file-handler';
                    Html::addCssClass($attrs, 'dropdown-item');
                    $itemLabel = \yii\helpers\ArrayHelper::remove($attrs, 'label', '');
                    if (isset($attrs['url'])) {
                        $attrs['href'] = \yii\helpers\ArrayHelper::remove($attrs, 'url', '#');
                    }
                    $output .= Html::beginTag('li') . Html::tag('a', $itemLabel, $attrs) . Html::endTag('li');
                }
                $output .= Html::endTag('ul');
            }
            $output .= Html::endTag('div');
            return $output;
        };
        echo $renderHandlerButtons($viewHandler);
        echo $renderHandlerButtons($exportHandler);
        echo $renderHandlerButtons(array_merge($editHandler, $importHandler));
        ?>
    </div>

<?php Modal::endDialog(); ?>
