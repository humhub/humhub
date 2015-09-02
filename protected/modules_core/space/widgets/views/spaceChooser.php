<li class="dropdown">
    <a href="#" id="space-menu" class="dropdown-toggle" data-toggle="dropdown">
        <!-- start: Show space image and name if chosen -->
        <?php if (Yii::app()->params['currentSpace']) { ?>
            <img
                src="<?php echo Yii::app()->params['currentSpace']->getProfileImage()->getUrl(); ?>"
                width="32" height="32" alt="32x32" data-src="holder.js/24x24"
                style="width: 32px; height: 32px; margin-right: 3px; margin-top: 3px;" class="img-rounded"/>
            <?php } ?>

        <?php
        if (Yii::app()->params['currentSpace']) {
        } else {
            echo '<i class="fa fa-dot-circle-o"></i><br>' . Yii::t('SpaceModule.widgets_views_spaceChooser', 'My spaces');
        }
        ?>
        <!-- end: Show space image and name if chosen -->
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu" id="space-menu-dropdown">
        <li>
            <form action="" class="dropdown-controls"><input type="text" id="space-menu-search"
                                                             class="form-control"
                                                             autocomplete="off"
                                                             placeholder="<?php echo Yii::t('SpaceModule.widgets_views_spaceChooser','Search'); ?>">

                <div class="search-reset" id="space-search-reset"><i
                        class="fa fa-times-circle"></i></div>
            </form>
        </li>

        <li class="divider"></li>
        <li>
            <ul class="media-list notLoaded" id="space-menu-spaces">
                <li id="loader_spaces">
                    <div class="loader">
                        <div class="sk-spinner sk-spinner-three-bounce">
                            <div class="sk-bounce1"></div>
                            <div class="sk-bounce2"></div>
                            <div class="sk-bounce3"></div>
                        </div>
                    </div>
                </li>
            </ul>
        </li>
        <?php if (Yii::app()->user->canCreateSpace()): ?>
            <li>
                <div class="dropdown-footer">
                    <?php
                    echo CHtml::link(Yii::t('SpaceModule.widgets_views_spaceChooser', 'Create new space'), $this->createUrl('//space/create/create'), array('class' => 'btn btn-info col-md-12', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
                    ?>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</li>

<script type="text/javascript">

    // set niceScroll to SpaceChooser menu
    $("#space-menu-spaces").niceScroll({
        cursorwidth: "7",
        cursorborder: "",
        cursorcolor: "#555",
        cursoropacitymax: "0.2",
        railpadding: {top: 0, right: 3, left: 0, bottom: 0}
    });

</script>
