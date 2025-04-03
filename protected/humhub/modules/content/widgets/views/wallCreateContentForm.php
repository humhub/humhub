<?php

use humhub\modules\content\assets\ContentFormAsset;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\content\widgets\WallCreateContentMenu;
use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $wallCreateContentForm WallCreateContentForm */
/* @var $defaultVisibility int */
/* @var $contentContainer ContentContainerActiveRecord */

ContentFormAsset::register($this);

$this->registerJsConfig('content.form', [
    'defaultVisibility' => $defaultVisibility,
    'disabled' => ($contentContainer instanceof Space && $contentContainer->isArchived()),
    'text' => [
        'makePrivate' => Yii::t('ContentModule.base', 'Change to "Private"'),
        'makePublic' => Yii::t('ContentModule.base', 'Change to "Public"'),
        'info.archived' => Yii::t('ContentModule.base', 'This space is archived.'),
    ],
    'redirectToContentContainerUrl' => Url::to(['/content/content/redirect-to-content-container', 'contentId' => 'the-content-id']),
]);
?>

<?php if (WallCreateContentMenu::canCreateEntry($contentContainer, 'form')) : ?>
    <div class="panel panel-default clearfix">
        <div class="panel-body" id="contentFormBody<?= $wallCreateContentForm->isModal ? 'Modal' : '' ?>" class="content-form-body" style="display:none;"
             data-action-component="content.form.CreateForm">
            <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

            <?= $wallCreateContentForm->renderActiveForm($form) ?>

            <?php ActiveForm::end(); ?>
        </div><!-- /panel body -->
    </div><!-- /panel -->
<?php endif; ?>
