<?php
/* @var $ancestorId string */
/* @var $ancestorClass string */
/* @var $ancestorStyle string */
/* @var $jplayerId string */
?>
<div id="<?= $jplayerId ?>" class="cp-jplayer"></div>
<div id="<?= $ancestorId ?>"<?php echo $ancestorClass ? " class=\"{$ancestorClass}\"" : ''; ?><?php echo $ancestorStyle ? " style=\"{$ancestorStyle}\"" : ''; ?>>
    <div class="cp-buffer-holder"> <!-- .cp-gt50 only needed when buffer is > than 50% -->
        <div class="cp-buffer-1"></div>
        <div class="cp-buffer-2"></div>
    </div>
    <div class="cp-progress-holder"> <!-- .cp-gt50 only needed when progress is > than 50% -->
        <div class="cp-progress-1"></div>
        <div class="cp-progress-2"></div>
    </div>
    <div class="cp-circle-control"></div>
    <ul class="cp-controls">
        <li><a class="cp-play" tabindex="1">play</a></li>
        <li><a class="cp-pause" style="display:none;" tabindex="1">pause</a></li> <!-- Needs the inline style here, or jQuery.show() uses display:inline instead of display:block -->
    </ul>
</div>