<?php

use humhub\widgets\DataSaved;
use humhub\widgets\ActiveForm;
use humhub\libs\Html;
use humhub\modules\space\widgets\Image;
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>

<strong><?= Yii::t('UserModule.account', 'Your account cannot be deleted!'); ?></strong><br />
<br />
<?= Yii::t('UserModule.account', 'You are currently the owner of following spaces:'); ?><br />

<?php foreach ($ownSpaces as $space): ?>
    <div class="media">
        <div class="media-left" style="padding-right:6px">
            <?= Image::widget(['space' => $space, 'width' => 38, 'link' => true]); ?>
        </div>
        <div class="media-body">
            <h4 class="media-heading"><?= Html::containerLink($space); ?></h4>
            <?= Yii::t('SpaceModule.base', '{count} members', ['count' => $space->getMemberships()->count()]); ?>
        </div>
    </div>    
<?php endforeach; ?>
<br />

<strong><?= Yii::t('UserModule.account', 'You must transfer ownership or delete these spaces before you can delete your account.'); ?></strong><br />
<br />
<?php $this->endContent(); ?>