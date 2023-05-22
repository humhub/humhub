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
 * @var $canCreateSpace boolean
 * @var $canAccessDirectory boolean
 *
 * @var $renderedItems string
 */

?>

<li class="dropdown">
    <a href="#" id="space-menu" class="dropdown-toggle" data-toggle="dropdown">
        <!-- start: Show space image and name if chosen -->
        <?php if ($currentSpace) : ?>
            <?= Image::widget(['space' => $currentSpace, 'width' => 32, 'htmlOptions' => ['class' => 'current-space-image']]); ?>
            <b class="caret"></b>
        <?php endif; ?>

        <?php if (!$currentSpace) : ?>
            <?= $noSpaceHtml ?>
        <?php endif; ?>
        <!-- end: Show space image and name if chosen -->
    </a>

    <ul class="dropdown-menu" id="space-menu-dropdown">
        <li>
            <form action="" class="dropdown-controls">
                <div <?= $canAccessDirectory ? 'class="input-group"' : '' ?>>
                    <input type="text" id="space-menu-search" class="form-control" autocomplete="off"
                           placeholder="<?= Yii::t('SpaceModule.chooser', 'Search') ?>"
                           title="<?= Yii::t('SpaceModule.chooser', 'Search for spaces') ?>">
                    <?php if ($canAccessDirectory) : ?>
                        <span id="space-directory-link" class="input-group-addon">
                            <a href="<?= Url::to(['/space/spaces']) ?>">
                                <?= Icon::get('directory') ?>
                            </a>
                        </span>
                    <?php endif; ?>
                    <div class="search-reset" id="space-search-reset"><?= Icon::get('times-circle') ?></div>
                </div>
            </form>
        </li>

        <li class="divider"></li>
        <li>
            <ul class="media-list notLoaded" id="space-menu-spaces">
                <?= $renderedItems ?>
            </ul>
        </li>
        <li class="remoteSearch">
            <ul id="space-menu-remote-search" class="media-list notLoaded"></ul>
        </li>

        <?php if ($canCreateSpace) : ?>
            <li>
                <div class="dropdown-footer">
                    <a href="#" class="btn btn-info col-md-12" data-action-click="ui.modal.load"
                       data-action-url="<?= Url::to(['/space/create/create']) ?>">
                        <?= Yii::t('SpaceModule.chooser', 'Create Space') ?>
                    </a>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</li>
