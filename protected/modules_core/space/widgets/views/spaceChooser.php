<li class="dropdown">
    <a href="#" id="space-menu" class="dropdown-toggle" data-toggle="dropdown">
        <!-- start: Show space image and name if chosen -->
        <?php if (Yii::app()->params['currentSpace']) { ?>
            <img
                src="<?php echo Yii::app()->params['currentSpace']->getProfileImage()->getUrl(); ?>"
                width="20" height="20" alt="20x20" data-src="holder.js/20x20"
                style="width: 20px; height: 20px; margin-right: 5px;" class="img-rounded"/>
        <?php } ?>

        <?php
        if (Yii::app()->params['currentSpace']) {
            echo '<span class="title">';
            echo Helpers::trimText(Yii::app()->params['currentSpace']->name, 30);
            echo ' </span>';
        } else {
            echo Yii::t('base', 'Choose a space... ');
        }
        ?>
        <!-- end: Show space image and name if chosen -->
        <b class="caret"></b></a>
    <ul class="dropdown-menu" id="space-menu-dropdown">
        <li>
            <form action="" class="dropdown-controls"><input type="text" id="space-menu-search"
                                                             class="form-control"
                                                             autocomplete="off"
                                                             placeholder="Search">

                <div class="search-reset" id="space-search-reset"><i
                        class="fa fa-times-circle"></i></div>
            </form>
        </li>

        <li class="divider"></li>
        <li>
            <ul class="media-list" id="space-menu-spaces">
                <li id="loader_spaces">
                    <div class="loader"></div>
                </li>
            </ul>
        </li>
        <li>
            <div class="dropdown-footer">
                <!-- create new space -->
                <?php
                echo CHtml::link(Yii::t('SpaceModule.base', 'Create new space'), $this->createUrl('//space/create/create'), array('class' => 'btn btn-info col-md-12', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
                ?>
            </div>
        </li>
    </ul>
</li>

<script type="text/javascript">

    // set niceScroll to SpaceChooser menu
    $("#space-menu-spaces").niceScroll({
        cursorwidth: "7",
        cursorborder:"",
        cursorcolor:"#555",
        cursoropacitymax:"0.2",
        railpadding:{top:0,right:3,left:0,bottom:0}
    });

</script>