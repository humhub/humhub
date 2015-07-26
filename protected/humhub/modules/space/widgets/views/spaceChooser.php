<?php
/* @var $this \humhub\components\WebView */
/* @var $currentSpace \humhub\modules\space\models\Space */

use yii\helpers\Url;
use yii\helpers\Html;

$this->registerJsFile("@web/resources/space/spacechooser.js");
?>


<h4>KATEGORIE</h4>
<div class="list-group">
    <?php foreach ($memberships as $membership): ?>
        <?php $newItems = $membership->countNewItems(); ?>
        <a href="<?php echo $membership->space->getUrl(); ?>" class="list-group-item">
            <img class="img-rounded" alt="16x16" src="<?php echo $membership->space->getProfileImage()->getUrl(); ?>"
                 style="width: 16px; height: 16px;">
            <?php echo Html::encode($membership->space->name); ?>
            <?php if ($newItems != 0): ?>
                <span class="badge"><?php echo $newItems; ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

