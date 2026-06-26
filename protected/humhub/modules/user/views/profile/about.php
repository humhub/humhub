<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\user\models\fieldtype\MarkdownEditor;

/**
 * @var $this View
 * @var $user \humhub\modules\user\models\User
 */
$categories = $user->profile->getProfileFieldCategories();
?>
<div id="user-profile-about" class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('UserModule.profile', '<strong>About</strong> this user') ?>
    </div>

    <div class="panel-body">
        <?php $isFirst = true; ?>
        <?php if (count($categories) > 1): ?>
            <ul id="tabs" class="nav nav-tabs" data-tabs="tabs" role="tablist">
                <?php foreach ($categories as $category): ?>
                    <li class="nav-item" role="presentation">
                        <a href="#profile-category-<?= $category->id ?>"
                           id="profile-category-tab-<?= $category->id ?>"
                           class="nav-link<?= $isFirst ? ' active' : '' ?>"
                           role="tab"
                           aria-selected="<?= $isFirst ? 'true' : 'false' ?>"
                           aria-controls="profile-category-<?= $category->id ?>"
                           data-bs-toggle="tab"><?= Html::encode(Yii::t($category->getTranslationCategory(), $category->title)) ?></a>
                    </li>
                    <?php $isFirst = false; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="tab-content">
            <?php $isFirst = true; ?>
            <?php foreach ($categories as $category): ?>
                <div class="tab-pane <?= $isFirst ? ' active' : '' ?> container gx-0 overflow-x-hidden"
                     id="profile-category-<?= $category->id ?>"
                     role="tabpanel"
                     aria-labelledby="profile-category-tab-<?= $category->id ?>">
                    <?php foreach ($user->profile->getProfileFields($category) as $field) : ?>
                        <div class="profile-item row mt-3" data-profile-field-internal-name="<?= $field->internal_name ?>">
                            <dt class="col-md-3 field-title text-lg-end">
                                <?= Html::encode(Yii::t($field->getTranslationCategory(), $field->title)) ?>
                            </dt>
                            <div class="col-md-9 field-value">
                                <?= ($field->field_type_class === MarkdownEditor::class) ?
                                    RichText::output($field->getUserValue($user, true, false)) :
                                    $field->getUserValue($user, false) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php $isFirst = false; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
