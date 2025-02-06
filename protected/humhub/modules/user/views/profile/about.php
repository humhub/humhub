<?php

use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\models\fieldtype\MarkdownEditor;
use yii\helpers\Html;

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
            <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
                <?php foreach ($categories as $category): ?>
                    <li <?= $isFirst ? 'class="active"' : '' ?>>
                        <a href="#profile-category-<?= $category->id ?>" data-toggle="tab">
                            <?= Html::encode(Yii::t($category->getTranslationCategory(), $category->title)) ?>
                        </a>
                    </li>
                    <?php $isFirst = false; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="tab-content">
            <?php $isFirst = true; ?>
            <?php foreach ($categories as $category): ?>
                <div class="tab-pane <?= $isFirst ? ' active' : '' ?>" id="profile-category-<?= $category->id ?>">
                    <?php foreach ($user->profile->getProfileFields($category) as $field) : ?>
                        <div class="profile-item row" data-profile-field-internal-name="<?= $field->internal_name ?>">
                            <label class="col-sm-3 field-title text-right">
                                <?= Html::encode(Yii::t($field->getTranslationCategory(), $field->title)) ?>
                            </label>
                            <div class="col-sm-9 field-value">
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
