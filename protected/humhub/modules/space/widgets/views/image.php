<?php
use yii\bootstrap\Html;

?>

<?php if ($link == true) : ?>
    <?php echo Html::beginTag('a', $linkOptions); ?>
<?php endif; ?>

<?php
echo Html::beginTag('div', $acronymHtmlOptions);
echo $acronym;
echo Html::endTag('div');
?>

<?php
echo Html::img($space->getProfileImage()->getUrl(), $imageHtmlOptions);
?>

<?php if ($link == true) : ?>
    <?php echo Html::endTag('a'); ?>
<?php endif; ?>


