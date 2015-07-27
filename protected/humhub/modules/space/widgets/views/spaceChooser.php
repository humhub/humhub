<?php
/* @var $this \humhub\components\WebView */
/* @var $currentSpace \humhub\modules\space\models\Space */

use yii\helpers\Url;
use yii\helpers\Html;

$this->registerJsFile("@web/resources/space/spacechooser.js");
?>

<div class="list-group" id="space-chooser">
    <div class="list-group-title">Category</div>
    <?php foreach ($memberships as $membership): ?>
        <?php $newItems = $membership->countNewItems(); ?>
        <a href="<?php echo $membership->space->getUrl(); ?>" class="list-group-item <?php if ($currentSpace != null) {
            if ($currentSpace->guid == $membership->space->guid) {
                echo 'active';
            }
        } ?>">
            <img class="img-rounded" alt="16x16" src="<?php echo $membership->space->getProfileImage()->getUrl(); ?>"
                 style="width: 16px; height: 16px;">
            <?php echo Html::encode($membership->space->name); ?>
            <?php if ($newItems != 0): ?>
                <span class="badge"><?php echo $newItems; ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>


<!--<div class="list-group" id="space-chooser">
    <div class="list-group-title">KATEGORIE</div>
    <?php /*foreach ($memberships as $membership): */?>
        <?php /*$newItems = $membership->countNewItems(); */?>
        <a href="<?php /*echo $membership->space->getUrl(); */?>" class="list-group-item <?php /*if ($currentSpace != null) {
            if ($currentSpace->guid == $membership->space->guid) {
                echo 'active';
            }
        } */?>">
            <div class="media">
                <div class="media-left">
                        <img class="media-object" src="<?php /*echo $membership->space->getProfileImage()->getUrl(); */?>" data-holder-rendered="true" style="width: 24px; height: 24px;">
                </div>
                <div class="media-body">
                    <?php /*if ($newItems != 0): */?>
                        <span class="badge pull-right"><?php /*echo $newItems; */?></span>
                    <?php /*endif; */?>
                    <h4 class="media-heading"><?php /*echo Html::encode($membership->space->name); */?></h4>
                    <div style="color: #bebebe;font-size: 11px;margin: 0;font-weight: 400;">Your first sample space to discover...</div>
                </div>
            </div>
        </a>
    <?php /*endforeach; */?>
</div>
-->




