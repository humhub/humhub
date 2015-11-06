<?php
$defaultImage = (basename($space->getProfileImage()->getUrl()) == 'default_space.jpg' || basename($space->getProfileImage()->getUrl()) == 'default_space.jpg?cacheId=0') ? true : false;
?>

<?php
if ($space->color != null) {
     $color = $space->color;
} else {
     $color = '#d7d7d7';
}


?>


<div class="space-profile-acronym-<?= $space->id; ?> space-acronym <?= $cssAcronymClass; ?> <?php if (!$defaultImage) : ?>hidden<?php endif; ?>"
     style="background-color: <?= $color; ?>; width: <?= $width; ?>px; height: <?= $height; ?>px;"><?php echo $acronym; ?></div>

<img class="space-profile-image-<?= $space->id; ?> img-rounded profile-user-photo <?= $cssImageClass; ?> <?php if ($defaultImage) : ?>hidden<?php endif; ?>"
     src="<?php echo $space->getProfileImage()->getUrl(); ?>"
     alt="<?= $space->name; ?>" style="width: <?= $width; ?>px; height: <?= $height; ?>px;"/>

