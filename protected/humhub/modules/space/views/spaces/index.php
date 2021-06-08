<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\DirectoryAsset;
use humhub\modules\space\components\SpaceDirectoryQuery;
use humhub\modules\space\widgets\SpaceDirectoryCard;
use humhub\modules\space\widgets\SpaceDirectoryFilters;
use humhub\widgets\Button;
use humhub\widgets\LinkPager;
use yii\web\View;

/* @var $this View */
/* @var $spaces SpaceDirectoryQuery */

DirectoryAsset::register($this);
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?= Yii::t('SpaceModule.base', '<strong>Spaces</strong>'); ?>
    </div>

    <div class="panel-body">
        <?= SpaceDirectoryFilters::widget(); ?>
    </div>

</div>

<div class="row cards">
    <?php if (!$spaces->exists()): ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <strong><?= Yii::t('SpaceModule.base', 'No results found!'); ?></strong><br/>
                <?= Yii::t('SpaceModule.base', 'Try other keywords or remove filters.'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($spaces->all() as $space) : ?>
        <?= SpaceDirectoryCard::widget(['space' => $space]); ?>
    <?php endforeach; ?>
</div>

<?php if (!$spaces->isLastPage()) : ?>
<div class="directory-load-more">
    <?= Button::primary(Yii::t('SpaceModule.base', 'Load more'))
        ->icon('fa-angle-down')
        ->action('directory.loadMore')
        ->options([
            'data-current-page' => $spaces->pagination->getPage() + 1,
            'data-total-pages' => $spaces->pagination->getPageCount(),
        ]) ?>
</div>
<?php endif; ?>
