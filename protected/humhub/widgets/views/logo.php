<?php

use yii\helpers\Html;
?>
<?php if ($place == "topMenu") : ?>
    <?php if ($logo->hasImage()) : ?>
        <a class="navbar-brand hidden-xs" style="height: 50px; padding:5px;"
           href="<?php echo Yii::$app->homeUrl; ?>">
            <img class="img-rounded" src="<?php echo $logo->getUrl(); ?>"
                 id="img-logo"/>
        </a>
    <?php endif; ?>
    <a class="navbar-brand" style="<?php if ($logo->hasImage()) : ?>display:none;<?php endif; ?> "
       href="<?php echo Yii::$app->homeUrl; ?>" id="text-logo">
           <?php echo Html::encode(Yii::$app->name); ?>
    </a>
<?php endif; ?>

<?php if ($place == "login") : ?>
    <?php if ($logo->hasImage()) : ?>
        <a href="<?php echo Yii::$app->homeUrl; ?>">
            <img class="img-rounded" src="<?php echo $logo->getUrl(); ?>"
                 id="img-logo"/>
        </a>
        <br>
    <?php else: ?>
        <h1 id="app-title" class="animated fadeIn"><?php echo Html::encode(Yii::$app->name); ?></h1>
    <?php endif; ?>
<?php endif; ?>






