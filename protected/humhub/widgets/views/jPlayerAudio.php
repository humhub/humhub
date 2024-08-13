<?php

use yii\helpers\Html;
?>


<?= Html::tag('div', null, $options); ?>

<div id="<?= $containerId ?>" class="jp-audio" style="border-radius:4px;" role="application" aria-label="media player">
    <div class="jp-type-playlist">
        <div class="jp-gui jp-interface" style="border-radius:4px;">
            <div class="jp-gui jp-interface" style="border-radius:4px;">
                <div class="jp-controls">
                    <button class="jp-previous" role="button" tabindex="0">previous</button>
                    <button class="jp-play" role="button" tabindex="0">play</button>
                    <button class="jp-next" role="button" tabindex="0">next</button>
                    <button class="jp-stop" role="button" tabindex="0">stop</button>
                </div>
                <div class="jp-progress">
                    <div class="jp-seek-bar" style="width: 100%;">
                        <div class="jp-play-bar" style="width: 0%;"></div>
                    </div>
                </div>
                <div class="jp-volume-controls pull right">
                    <button class="jp-mute" role="button" tabindex="0">mute</button>
                    <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
                    <div class="jp-volume-bar">
                        <div class="jp-volume-bar-value" style="width: 80%;"></div>
                    </div>
                </div>
                <div class="jp-time-holder">
                    <div class="jp-current-time" role="timer" aria-label="time">00:00</div>
                    <div class="jp-duration" role="timer" aria-label="duration">02:56</div>
                </div>
                <div class="jp-toggles">
                    <button class="jp-repeat" role="button" tabindex="0">repeat</button>
                    <button class="jp-shuffle" role="button" tabindex="0">shuffle</button>
                </div>
            </div>
        </div>
        <div class="jp-playlist" style="border-bottom-left-radius:4px;border-bottom-right-radius:4px;">
            <ul></ul>
        </div>
        <div class="jp-no-solution" style="display: none;">
            <span>Update Required</span>
            To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
        </div>
    </div>
</div>