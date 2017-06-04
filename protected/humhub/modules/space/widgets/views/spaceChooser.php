<?php
/* @var $this \humhub\components\WebView */
/* @var $currentSpace \humhub\modules\space\models\Space */

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\space\widgets\SpaceChooserItem;

\humhub\modules\space\assets\SpaceChooserAsset::register($this);

$noSpaceView = '<div class="no-space"><i class="fa fa-dot-circle-o"></i><br>' . Yii::t('SpaceModule.widgets_views_spaceChooser', 'My spaces') . '<b class="caret"></b></div>';

$this->registerJsConfig('space.chooser', [
    'noSpace' => $noSpaceView,
    'remoteSearchUrl' =>  Url::to(['/space/browse/search-json']),
    'text' => [
        'info.remoteAtLeastInput' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'To search for other spaces, type at least {count} characters.', ['count' => 2]),
        'info.emptyOwnResult' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'No member or following spaces found.'),
        'info.emptyResult' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'No result found for the given filter.'),
    ],
]);
?>

<li class="dropdown">
    <a href="#" id="space-menu" class="dropdown-toggle" data-toggle="dropdown">
        <!-- start: Show space image and name if chosen -->
        <?php if ($currentSpace) : ?>
            <?=
            \humhub\modules\space\widgets\Image::widget([
                'space' => $currentSpace,
                'width' => 32,
                'htmlOptions' => [
                    'class' => 'current-space-image',
            ]]);
            ?>
            <b class="caret"></b>
        <?php endif; ?>

        <?php if (!$currentSpace) : ?>
            <?= $noSpaceView ?>
        <?php endif; ?>
        <!-- end: Show space image and name if chosen -->

    </a>
    <ul class="dropdown-menu" id="space-menu-dropdown">
        <li>
            <form action="" class="dropdown-controls">
                <div class="input-group">
                    <input type="text" id="space-menu-search" class="form-control" autocomplete="off" 
                           placeholder="<?= Yii::t('SpaceModule.widgets_views_spaceChooser', 'Search'); ?>"
                           title="<?= Yii::t('SpaceModule.widgets_views_spaceChooser', 'Search for spaces'); ?>">
                    <span id="space-directory-link" class="input-group-addon" >
                        <a href="<?= Url::to(['/directory/directory/spaces']); ?>">
                        <i class="fa fa-book"></i>
                        </a>
                    </span>
                    <div class="search-reset" id="space-search-reset"><i class="fa fa-times-circle"></i></div>
                </div>
            </form>
        </li>

        <li class="divider"></li>
        <li>
            <ul class="media-list notLoaded" id="space-menu-spaces">
                <?php foreach ($memberships as $membership): ?>
                    <?= SpaceChooserItem::widget(['space' => $membership->space, 'updateCount' => $membership->countNewItems(), 'isMember' => true]); ?>
                <?php endforeach; ?>
                <?php foreach ($followSpaces as $followSpace): ?>
                    <?= SpaceChooserItem::widget(['space' => $followSpace, 'isFollowing' => true]); ?>
                <?php endforeach; ?>
            </ul>
        </li>
        <li class="remoteSearch">
            <ul id="space-menu-remote-search" class="media-list notLoaded"></ul>
        </li>

    <?php if ($canCreateSpace): ?>
        <li>
            <div class="dropdown-footer">
                <a href="#" class="btn btn-info col-md-12" data-action-click="ui.modal.load" data-action-url="<?= Url::to(['/space/create/create']) ?>">
                    <?= Yii::t('SpaceModule.widgets_views_spaceChooser', 'Create new space') ?>
                </a>
            </div>
        </li>
    <?php endif; ?>
    </ul>
</li>
