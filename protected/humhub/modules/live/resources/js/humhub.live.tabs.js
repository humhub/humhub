humhub.module('live.tabs', function (module, require, $) {

    var TabsController = function() {
        this.becomeMaster();
        window.addEventListener('storage', this, false);
        window.addEventListener('unload', this, false);
        window.addEventListener('blur', this, false);
        window.addEventListener('focus', this, false);
    };

    TabsController.prototype.isMaster = false;
    TabsController.prototype.destroy = function () {
        if (this.isMaster) {
            localStorage.setItem('live.ping', 0);
        }
        window.removeEventListener('storage', this, false);
        window.removeEventListener('unload', this, false);
    };

    TabsController.prototype.handleEvent = function (event) {
        if (event.type === 'blur') {
            this.loseMaster();
        }
        if (event.type === 'focus') {
            this.becomeMaster();
        }
        if (event.type === 'storage' && ! this.isMaster) {
            switch (event.key) {
                case 'live.payload':
                    var payload = JSON.parse(localStorage.getItem('live.payload'));
                    if (payload) {
                        console.log('modified');
                        this.updateFromLocalStorage(payload);
                    }
            }
        }
        if (event.type === 'unload') {
            this.destroy();
        } else {
            var type = event.key,
                ping = 0;
            if (type === 'live.ping') {
                ping = +localStorage.getItem('live.ping') || 0;
                if (ping) {
                    this.loseMaster();
                } else {
                    clearTimeout(this._ping);
                    this._ping = setTimeout(
                        this.becomeMaster.bind(this),
                        ~~(Math.random() * 1000)
                    );
                }
            }
        }
    };

    TabsController.prototype.becomeMaster = function () {
        localStorage.setItem('live.ping', Date.now());
        clearTimeout(this._ping);
        this._ping = setTimeout(
            this.becomeMaster.bind(this),
            10000 + ~~(Math.random() * 10000)
        );
        this.isMaster = true;
    };

    TabsController.prototype.loseMaster = function () {
        clearTimeout(this._ping);
        this._ping = setTimeout(
            this.becomeMaster.bind(this),
            15000 + ~~(Math.random() * 20000 )
        );
        this.isMaster = false;
    };

    TabsController.prototype.broadcast = function (data) {
        localStorage.setItem('live.payload', JSON.stringify(data));
    };

    module.export({
        TabsController: TabsController
    })
});
