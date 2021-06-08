<?php

use humhub\assets\DirectoryAsset;
use humhub\libs\Html;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\widgets\PeopleCard;
use humhub\modules\user\widgets\PeopleFilters;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;

/* @var $this \yii\web\View */
/* @var $people PeopleQuery */
/* @var $showInviteButton bool */

DirectoryAsset::register($this);
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php if ($people->isFilteredByGroup()) : ?>
            <?= Yii::t('UserModule.base', '<strong>Group</strong> members - {group}', ['{group}' => Html::encode($people->filteredGroup->name)]); ?>
        <?php else: ?>
            <?= Yii::t('UserModule.base', '<strong>People</strong>'); ?>
        <?php endif; ?>

        <?php if ($showInviteButton): ?>
            <?= ModalButton::primary(Yii::t('UserModule.base', 'Send invite'))
                ->load(['/user/invite'])->icon('invite')->sm()->right() ?>
        <?php endif; ?>
    </div>

    <div class="panel-body">
        <?= PeopleFilters::widget(); ?>
    </div>

</div>

<div class="row cards">
    <?php if (!$people->exists()): ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <strong><?= Yii::t('UserModule.base', 'No results found!'); ?></strong><br/>
                    <?= Yii::t('UserModule.base', 'Try other keywords or remove filters.'); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($people->all() as $user) : ?>
        <?= PeopleCard::widget(['user' => $user]); ?>
    <?php endforeach; ?>
</div>

<?php if (!$people->isLastPage()) : ?>
    <div class="directory-load-more">
        <?= Button::primary(Yii::t('UserModule.base', 'Load more'))
            ->icon('fa-angle-down')
            ->action('directory.loadMore')
            ->options([
                'data-current-page' => $people->pagination->getPage() + 1,
                'data-total-pages' => $people->pagination->getPageCount(),
            ]) ?>
    </div>
<?php endif; ?>
