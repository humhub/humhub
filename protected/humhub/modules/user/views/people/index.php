<?php
use humhub\libs\Html;
use humhub\modules\user\widgets\PeopleActionsButton;
use humhub\modules\user\widgets\Image;
use humhub\modules\user\widgets\PeopleTagList;
use humhub\widgets\LinkPager;
use humhub\widgets\ModalButton;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $keyword string */
/* @var $group humhub\modules\user\models\Group */
/* @var $users humhub\modules\user\models\User[] */
/* @var $pagination yii\data\Pagination */
/* @var $showInviteButton bool */
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php if ($group === null) : ?>
            <?= Yii::t('UserModule.base', '<strong>Member</strong> directory'); ?>
        <?php else: ?>
            <?= Yii::t('UserModule.base', '<strong>Group</strong> members - {group}', ['{group}' => Html::encode($group->name)]); ?>
        <?php endif; ?>

        <?php if ($showInviteButton): ?>
            <?= ModalButton::primary(Yii::t('UserModule.base', 'Send invite'))
                ->load(['/user/invite'])->icon('invite')->sm()->right() ?>
        <?php endif; ?>
    </div>

    <div class="panel-body">
        <?= Html::beginForm('', 'get', ['class' => 'form-search']); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Free text search in the directory (name, first name, telephone number, etc.)') ?></div>
                    <?= Html::hiddenInput('page', '1'); ?>
                    <?= Html::textInput("keyword", $keyword, ['class' => 'form-control form-search', 'placeholder' => Yii::t('UserModule.base', 'search for members')]); ?>
                    <?= Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']); ?>
                </div>
            </div>
            <div class="col-md-6">
                <?= Html::a(Yii::t('UserModule.base', 'Reset filter'), Url::to(['/user/people']), ['class' => 'form-search-reset']); ?>
            </div>
        </div>
        <?= Html::endForm(); ?>
    </div>

</div>

<div class="row">
    <?php if (count($users) == 0): ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Yii::t('UserModule.base', 'No people found!'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($users as $user) : ?>
    <div class="card card-people">
        <div class="card-front">
            <div class="card-image<?= $user->getProfileBannerImage()->hasImage() ? '' : ' card-no-image'; ?>" style="background-image: url('<?= $user->getProfileBannerImage()->getUrl() ?>')"></div>
            <div class="card-header">
                <?= Image::widget([
                    'user' => $user,
                    'linkOptions' => ['data-contentcontainer-id' => $user->contentcontainer_id],
                    'width' => 100,
                ]); ?>
                <div class="card-buttons">
                    <?= PeopleActionsButton::widget(['user' => $user]); ?>
                </div>
            </div>
            <div class="card-body">
                <h4><?= Html::containerLink($user); ?></h4>
                <h5><?= Html::encode($user->profile->title); ?></h5>
                <?= PeopleTagList::widget(['user' => $user]); ?>
            </div>
            <div class="card-footer">
                <a href="#" class="fa fa-mobile-phone"></a>
                <a href="#" class="fa fa-comment-o"></a>
                <a href="#" class="fa fa-envelope-o"></a>
                <a href="#" class="fa fa-video-camera"></a>
                <a href="#" class="fa fa-arrow-right pull-right"></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="pagination-container">
    <?= LinkPager::widget(['pagination' => $pagination]); ?>
</div>
