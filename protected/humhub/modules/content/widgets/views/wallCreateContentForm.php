<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\space\models\Space;

\humhub\modules\content\assets\ContentFormAsset::register($this);

?>

<?php 
    $this->registerJsConfig('content.form', [
        'defaultVisibility' => $defaultVisibility,
        'text' => [
            'makePrivate' => Yii::t('ContentModule.widgets_views_contentForm', 'Make private'),
            'makePublic' => Yii::t('ContentModule.widgets_views_contentForm', 'Make public')
        ]]);
?>

<div class="panel panel-default">
    <div class="panel-body" id="contentFormBody" style="display:none;" data-action-component="content.form.CreateForm" data-action-param="{defaultVisibility: <?= $defaultVisibility ?>}">
        <?php echo Html::beginForm($submitUrl, 'POST'); ?>
        <?php echo $form; ?>

        <div id="notifyUserContainer" class="form-group" style="margin-top: 15px;display:none;">

            <?php $memberPickerUrl = ($contentContainer instanceof Space) ? $contentContainer->createUrl('/space/membership/search') : null ?>
            <?= humhub\modules\user\widgets\UserPickerField::widget([
                'id' => 'notifyUserInput',
                'url' => $memberPickerUrl,
                'formName' => 'notifyUserInput',
                'maxSelection' => 10,
                'disabledItems' => [Yii::$app->user->guid],
                'placeholder' => Yii::t('ContentModule.widgets_views_contentForm', 'Add a member to notify'),
            ]);?>
        </div>

        <?php
        echo Html::hiddenInput("containerGuid", $contentContainer->guid);
        echo Html::hiddenInput("containerClass", get_class($contentContainer));
        ?>

        <ul id="contentFormError">
        </ul>
        
        <div class="contentForm_options">

            <hr>

            <div class="btn_container">
                <button id="post_submit_button" data-action-click="submit" data-action-submit data-ui-loader class="btn btn-info">
                    <?= $submitButtonText ?>
                </button>
 
                <?php
                // Creates Uploading Button
                echo humhub\modules\file\widgets\FileUploadButton::widget(array(
                    'uploaderId' => 'contentFormFiles',
                    'fileListFieldName' => 'fileList',
                ));
                ?>

                <!-- public checkbox -->
                <?php echo Html::checkbox("visibility", "", array('id' => 'contentForm_visibility', 'class' => 'contentForm hidden')); ?>

                <!-- content sharing -->
                <div class="pull-right">

                    <span class="label label-success label-public hidden"><?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Public'); ?></span>

                    <ul class="nav nav-pills preferences" style="right: 0; top: 5px;">
                        <li class="dropdown">
                            <a class="dropdown-toggle" style="padding: 5px 10px;" data-toggle="dropdown" href="#"><i
                                    class="fa fa-cogs"></i></a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a data-action-click="notifyUser">
                                        <i class="fa fa-bell"></i> <?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Notify members'); ?>
                                    </a>
                                </li>
                                <?php if ($canSwitchVisibility): ?>
                                    <li>
                                        <a id="contentForm_visibility_entry" data-action-click="changeVisibility">
                                            <i class="fa fa-unlock"></i> <?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Make public'); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>


                </div>

            </div>

            <?php
            // Creates a list of already uploaded Files
            echo \humhub\modules\file\widgets\FileUploadList::widget(array(
                'uploaderId' => 'contentFormFiles'
            ));
            ?>
        </div>
         <!-- /contentForm_Options -->
        <?php echo Html::endForm(); ?>

    </div>
    <!-- /panel body -->
</div> <!-- /panel -->

<div class="clearFloats"></div>