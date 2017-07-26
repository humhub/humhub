<div class="flexwrap flexbox">
    <div class="px-video-container" id="video<?=$videoId?>">
        <div class="px-video-img-captions-container">
            <div class="px-video-captions hide"></div>
            <div class="px-video-wrapper">
                <video poster="<?=$previewImageUrl?>" class="px-video" controls>
                    <!-- video files -->
                    <source src='<?=$videoSrcUrl?>' type='video/mp4' />

                    <!-- for future use: text track file -->
                    <!-- <track kind="captions" label="<?=$captionsLabel?>" src="<?=$captionsSrcUrl?>" srclang="<?=$captionsLanguage?>" default /> -->

                    <!-- fallback for browsers that don't support the video element -->
                    <a href="<?=$videoSrcUrl?>">
                        <img src="<?=$previewImageUrl?>" width="640" height="360" alt="<?=Yii::t('base', "Download video")?>" />
                    </a>
                </video>
            </div>
        </div><!-- end container for captions and video -->
        <div class="px-video-controls"></div>
    </div><!-- end video container -->
</div>

<script>
    // Initialize video container
    var video_<?= $videoId ?> = new InitPxVideo({
        "videoId": "video<?= $videoId ?>",
        "captionsOnDefault": false,
        "seekInterval": 20,
        "videoTitle": "<?= $videoTitle ?>",
        "debug": true
    });
</script>