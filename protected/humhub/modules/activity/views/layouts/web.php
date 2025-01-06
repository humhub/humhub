<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\TimeAgo;
use yii\helpers\Url;

/* @var $originator \humhub\modules\user\models\User */
/* @var $clickable bool */
/* @var $record ActiveRecord */

?>


<?php if ($clickable) : ?>
<a href="<?= Url::to(['/activity/link', 'id' => $record->id]) ?>">
    <?php endif; ?>

    <div class="d-flex">
        <div class="flex-shrink-0 me-2 img-profile-space">
            <?php if ($originator !== null) : ?>
                <!-- Show user image -->
                <?= $originator->getProfileImage()->render(32, ['link' => false]) ?>
            <?php endif; ?>

            <!-- Show space image, if you are outside from a space -->
            <?php if (
                !Yii::$app->controller instanceof ContentContainerController
                && $record->content->container instanceof Space
            ) : ?>
                <?= Image::widget([
                    'space' => $record->content->container,
                    'width' => 20,
                    'htmlOptions' => ['class' => 'img-space'],
                ]) ?>
            <?php endif; ?>
        </div>

        <div class="flex-grow-1 text-break">

            <!-- Show content -->
            <?= $content ?>
            <br>

            <!-- show time -->
            <?= TimeAgo::widget(['timestamp' => $record->content->created_at]) ?>
        </div>
    </div>

    <?php if ($clickable) : ?>
</a>
<?php endif; ?>
