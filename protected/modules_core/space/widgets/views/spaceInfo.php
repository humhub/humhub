<?php
/** @var $space Space */
?>

<div class="panel panel-default space-info" id="space-info-panel">

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'space-info-panel')); ?>

    <div class="panel-heading"><strong>Space</strong> info</div>

    <div class="panel-body">
        <div class="media-body">
            <div class="media">
                <img class="img-rounded pull-left"
                     src="<?php echo $space->getProfileImage()->getUrl(); ?>" height="80" width="80"
                     alt="80x80" data-src="holder.js/80x80" style="width: 80px; height: 80px;"/>
                <strong><?php echo $space->name; ?></strong>

                <div class="media-body" id="space-description"
                     style="overflow: hidden; max-height: 55px; font-size: 13px;">
                    <?php echo $space->description; ?>
                </div>
                <a class="btn btn-default btn-xs pull-right hidden" id="more-button" style="margin-top: 5px;"
                   href="javascript:showMoreInfo();"><i class="fa fa-arrow-down"></i> more</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">


    $(document).ready(function () {

        // save the count of characters
        var _words = '<?php echo strlen($space->description); ?>';


        if (_words > 60) {
            // show more-button
            $('#more-button').removeClass('hidden');
        }
    });

    // current button state
    var _state = "more";

    function showMoreInfo() {

        if (_state == "more") {
            $('#space-description').css('max-height', '2000px');
            $('#more-button').html('<i class="fa fa-arrow-up"></i> less');
            _state = "less"
        } else {
            $('#space-description').css('max-height', '55px');
            $('#more-button').html('<i class="fa fa-arrow-down"></i> more');
            _state = "more"
        }

    }

</script>