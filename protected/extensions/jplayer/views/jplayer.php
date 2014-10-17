<div id="audioplayer_<?php echo $id; ?>" class="audioplayer" style="display: none;">
    <div id="jquery_jplayer_<?php echo $id; ?>" class="jp-jplayer"></div>
    <div id="jp_container_<?php echo $id; ?>" class="jp-audio">
        <div class="jp-type-single">
            <div class="jp-gui jp-interface">

                    <div class="jp-controls">
                        <a href="javascript:;" class="jp-play" tabindex="1"><i class="fa fa-play"></i></a>
                        <a href="javascript:;" class="jp-pause" tabindex="1"><i class="fa fa-pause"></i></a>
                    </div>

                    <div class="jp-progress-container">
                        <div class="jp-current-time"></div>
                        <div class="jp-progress">
                            <div class="jp-seek-bar">
                                <div class="jp-play-bar"></div>
                            </div>
                        </div>
                        <div class="jp-duration"></div>
                    </div>

            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function(){

        $("#jquery_jplayer_<?php echo $id; ?>").jPlayer({
            ready: function () {
                $(this).jPlayer("setMedia", {
                    mp3: '<?php echo $file; ?>'
                });
            },
            cssSelectorAncestor: '#jp_container_<?php echo $id; ?>',
            swfPath: "/js",
            preload: 'metadata',
            volume: 0.5,
            supplied: "mp3"
        });

        $("#jquery_jplayer_<?php echo $id; ?>").bind($.jPlayer.event.play, function() {
            $(this).jPlayer("pauseOthers"); // pause all players except this one.
        });

        $('#audioplayer_<?php echo $id; ?>').css('display', 'inline-block');
        $('#audioplayer_<?php echo $id; ?>').addClass('animated fadeIn');

    });

</script>