humhub.module('tour', function (module, requrie, $) {

    var client = requrie('client');
    var page;
    var nextUrl;

    var start = function (options) {
        // Load driver.js
        const driver = window.driver.js.driver;
        const driverObj = driver({
            ...module.config.driverOptions,
            ...options.driver
        });
        driverObj.drive();

        page = options.page;
        nextUrl = options.nextUrl;
    };

    /**
     * Set tour as seen
     */
    function tourCompleted(next) {
        client.post(module.config.completedUrl, {data: {page: page}}).then(function () {
            // cross out welcome tour entry
            $('#tour-panel-' + module.config.dashboardPage).addClass('completed');

            if (next === true && nextUrl) {
                window.location.href = nextUrl;
            } else {
                window.location.href = module.config.dashboardUrl;
            }
        });
    }

    var next = function () {
        tourCompleted(true);
    };

    var hidePanel = function (event) {
        $(".panel-tour").slideToggle("slow");
        client.post(event)
    }

    module.export({
        start: start,
        next: next,
        hidePanel: hidePanel,
    });
});
