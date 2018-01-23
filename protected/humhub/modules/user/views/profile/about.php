<?php

use yii\helpers\Html;
use humhub\widgets\RichText;
use humhub\modules\user\models\fieldtype\MarkdownEditor;
use humhub\widgets\MarkdownView;
?>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('UserModule.views_profile_about', '<strong>About</strong> this user'); ?></div>
    <div class="panel-body">
        <?php $firstClass = "active"; ?>
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <?php foreach ($user->profile->getProfileFieldCategories() as $category): ?>
                <li class="<?php echo $firstClass; ?>">
                    <a href="#profile-category-<?php echo $category->id; ?>" data-toggle="tab"><?php echo Html::encode(Yii::t($category->getTranslationCategory(), $category->title)); ?></a>
                </li>
                <?php
                $firstClass = "";
            endforeach;
            ?>
        </ul>
        <?php $firstClass = "active"; ?>
        <div class="tab-content">
            <?php foreach ($user->profile->getProfileFieldCategories() as $category): ?>
                <div class="tab-pane <?php
                echo $firstClass;
                $firstClass = "";
                ?>" id="profile-category-<?php echo $category->id; ?>">
                    <form class="form-horizontal" role="form">
                        <?php foreach ($user->profile->getProfileFields($category) as $field) : ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">
                                    <?php echo Html::encode(Yii::t($field->getTranslationCategory(), $field->title)); ?>
                                </label>
                                <?php if (strtolower($field->title) == 'about'): ?>
                                    <div class="col-sm-9">
                                        <p class="form-control-static"><?php echo RichText::widget(['text' => $field->getUserValue($user, true)]); ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="col-sm-9">
                                        <?php if ($field->field_type_class == MarkdownEditor::className()): ?>
                                            <p class="form-control-static" style="min-height: 0 !important;padding-top:0;">
                                                <?= MarkdownView::widget(['markdown' => $field->getUserValue($user, false)]); ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="form-control-static"><?php echo $field->getUserValue($user, false); ?></p>                     
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
