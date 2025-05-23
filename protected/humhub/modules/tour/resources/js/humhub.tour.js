humhub.module('tour', function (module, requrie, $) {

    var client = requrie('client');
    var tourId;
    var nextUrl;

    var start = function (options) {
        tourId = options.tourId;
        nextUrl = options.nextUrl;

        // Load driver.js
        const driver = window.driver.js.driver;
        const driverObj = driver({
            ...module.config.driverJsOptions,
            ...options.driverJs,
            onDestroyed: (element, step, options) => {
                const next = nextUrl !== "" && nextUrl != null;
                tourCompleted(next);
            }
        });
        driverObj.drive();
    };

    /**
     * Set tour as seen
     */
    function tourCompleted(next) {
        client.post(module.config.completedUrl, {data: {tour_id: tourId}}).then(function () {
            // cross out welcome tour entry
            $('#tour-panel-' + module.config.dashboardTourId).addClass('completed');

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
