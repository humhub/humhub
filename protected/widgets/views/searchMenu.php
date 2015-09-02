<li>
    <form action="" class="dropdown-controls">
        <input type="text" id="search-menu-search"
               class="form-control"
               autocomplete="off"
               placeholder="<?php echo Yii::t('base', 'Search for users and spaces'); ?>">

        <div class="search-reset" id="search-search-reset"><i
                class="fa fa-times-circle"></i></div>
    </form>
</li>

<script type="text/javascript">
    /**
     * Open search menu
     */
    $('#search-menu-nav').click(function () {

        // use setIntervall to setting the focus
        var searchFocus = setInterval(setFocus, 10);

        function setFocus() {

            // set focus
            $('#search-menu-search').focus();
            // stop interval
            clearInterval(searchFocus);
        }

    })
</script>