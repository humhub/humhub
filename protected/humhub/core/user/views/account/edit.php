<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_edit', '<strong>User</strong> details'); ?>

    <!-- show flash message after saving -->
    <?php $this->widget('application.widgets.DataSavedWidget'); ?>
</div>
<div class="panel-body">

    <div id="profile-form-container" style="display: none;">
        <?php echo $form; ?>
    </div>
    <div id="profile-loader" class="loader">
        <div class="sk-spinner sk-spinner-three-bounce">
            <div class="sk-bounce1"></div>
            <div class="sk-bounce2"></div>
            <div class="sk-bounce3"></div>
        </div>
    </div>


</div>

<script type="text/javascript">

    $(document).ready(function () {

        // save the tab to show
        var activeTab = 0;

        // add tab content <div>
        $('#profile-form-container form').prepend('<div class="tab-content"></div>');

        // add clickable tabs
        $('#profile-form-container form').prepend('<ul id="profile-tabs" class="nav nav-tabs" data-tabs="tabs"></ul>');

        // go through all fieldsets with inputs (categories)
        $('#profile-form-container form fieldset legend').each(function (index, value) {

            // save current tab index by the first error to activate him later
            if (checkErrors($(this)) == true && activeTab == 0) {
                activeTab = index;
            }

            // save category text
            var _category = $(this).text();

            // build tab structure
            var _tab = '<li><a href="#category-' + index + '" data-toggle="tab">' + _category + '</a></li>';

            // add tab structure to tab
            $('#profile-tabs').append(_tab);

            // build tab content container
            var _tabContent = '<div class="tab-pane" id="category-' + index + '"></div>';

            // add content to tab content container
            $('.tab-content').append(_tabContent);

            // clone every inputs from original form
            var $inputs = $(this).parent().children(".form-group").clone();

            // add cloned inputs to current tab content container
            $('#category-' + index).html($inputs);

            // remove original inputs from original form
            $(this).parent().remove();

        })

        // add an <hr> between tab and submit button
        $('#profile-form-container form .form-group-buttons').before('<hr>');


        // check if errorSummary element exists
        if ($('.errorSummary').length != null) {

            // clone element
            var _errorSummary = $('.errorSummary').clone();

            // remove original element
            $('.errorSummary').remove();

            // add cloned element at the top
            $('#profile-form-container form').prepend(_errorSummary);

        }

        // activate the first tab or the tab with the first error
        $('#profile-tabs a[href="#category-' + activeTab + '"]').tab('show')

        // hide loader
        $('#profile-loader').hide();

        // show created tab element
        $('#profile-form-container').show();


    })


    /**
     * Check for errors in a specific category
     * @param _object
     * @returns {boolean}
     */
    function checkErrors(_object) {

        // save standard result
        var _error = false;

        // go through every input
        _object.parent().children(".form-group").each(function (index, value) {

            // if an input have the class "error"
            if ($(this).children('.form-control').hasClass("error")) {

                // change standard result
                _error = true;

                // stop loop/function
                return false;
            }
        })

        // return result
        return _error;

    }

</script>
