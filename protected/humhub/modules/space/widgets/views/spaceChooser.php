<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $currentSpace Space
 * @var $noSpaceHtml string
 *
 * @var $canCreateSpace bool
 * @var $canAccessDirectory bool
 *
 * @var $renderedItems string
 */

?>

<li class="nav-item dropdown">
    <a href="#" id="space-menu" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <!-- start: Show space image and name if chosen -->
        <?php if ($currentSpace) : ?>
            <?= Image::widget(['space' => $currentSpace, 'width' => 32, 'htmlOptions' => ['class' => 'current-space-image']]) ?>
        <?php endif; ?>

        <?php if (!$currentSpace) : ?>
            <?= $noSpaceHtml ?>
        <?php endif; ?>
        <!-- end: Show space image and name if chosen -->
    </a>

    <ul class="dropdown-menu" id="space-menu-dropdown">
        <li>
            <form action="" class="dropdown-header dropdown-controls">
                <div <?= $canAccessDirectory ? 'class="input-group"' : '' ?>>
                    <input type="text" id="space-menu-search" class="form-control" autocomplete="off"
                           placeholder="<?= Yii::t('SpaceModule.chooser', 'Search') ?>"
                           title="<?= Yii::t('SpaceModule.chooser', 'Search for spaces') ?>">
                    <?php if ($canAccessDirectory) : ?>
                        <span id="space-directory-link" class="input-group-text">
                            <a href="<?= Url::to(['/space/spaces']) ?>">
                                <?= Icon::get('directory') ?>
                            </a>
                        </span>
                    <?php endif; ?>
                    <div class="search-reset" id="space-search-reset"><?= Icon::get('times-circle') ?></div>
                </div>
            </form>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <div id="space-menu-spaces" class="notLoaded hh-list">
                <?= $renderedItems ?>
            </div>
        </li>
        <li class="remoteSearch">
            <div id="space-menu-remote-search" class="dropdown-item notLoaded hh-list"></div>
        </li>

        <?php if ($canCreateSpace) : ?>
            <li>
                <div class="dropdown-footer">
                    <a href="#" class="btn btn-info col-lg-12" data-action-click="ui.modal.load"
                       data-action-url="<?= Url::to(['/space/create/create']) ?>">
                        <?= Yii::t('SpaceModule.chooser', 'Create Space') ?>
                    </a>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</li>
