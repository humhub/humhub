<div class="panel panel-default space-info">
    <div class="panel-body">
        <div class="media-body">
            <h5 class="media-heading"><?php echo $space->name; ?></h5>

            <div class="media">
                <img class="img-rounded pull-left"
                     src="<?php echo $space->getProfileImage()->getUrl(); ?>" height="100" width="100"
                     alt="100x100" data-src="holder.js/100x100" style="width: 100px; height: 100px;"/>

                <div class="media-body" id="space-description" style="overflow: hidden; max-height: 75px; font-size: 13px;">
                    <?php echo $space->description; ?>
                </div>
                <a class="btn btn-default btn-xs pull-right hidden" id="more-button" style="margin-top: 5px;" href="javascript:showMore();"><i class="icon-arrow-down"></i> more</a>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function() {

        // save the count of characters
        var _words = '<?php echo strlen($space->description); ?>';


        if (_words > 60) {
            // show more-button
            $('#more-button').removeClass('hidden');
        }
    });

    // current button state
    var _state = "more";

    function showMore() {

        if (_state == "more") {
            $('#space-description').css('max-height', '2000px');
            $('#more-button').html('<i class="icon-arrow-up"></i> less');
            _state = "less"
        } else {
            $('#space-description').css('max-height', '75px');
            $('#more-button').html('<i class="icon-arrow-down"></i> more');
            _state = "more"
        }

    }

</script>