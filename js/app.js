/**
 * Holds all already loaded javascript libaries
 *
 * @type HashTable
 */
var currentLoadedJavaScripts = new HashTable();


/**
 * Looks for script tags inside the given string and checks if the files
 * are already loaded.
 *
 * When already loaded, the scripts will ignored.
 *
 * @returns {undefined}
 */
function parseHtml(htmlString) {
    var re = /<script type="text\/javascript" src="([\s\S]*?)"><\/script>/gm;

    var match;
    while (match = re.exec(htmlString)) {

        js = match[1];

        if (currentLoadedJavaScripts.hasItem(js)) {
            // Remove Script Tag
            //console.log("Ignore load of : "+js);
            htmlString = htmlString.replace(match[0], "");

        } else {
            // Let Script Tag
            //console.log("First load of: "+js);
            currentLoadedJavaScripts.setItem(js, 1);

        }

    }
    return htmlString;
}


/**
 * Hashtable
 *
 * Javscript Class which represents a hashtable.
 *
 * @param {type} obj
 * @returns {HashTable}
 */
function HashTable(obj) {
    this.length = 0;
    this.items = {};
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            this.items[p] = obj[p];
            this.length++;
        }
    }

    this.setItem = function(key, value) {
        var previous = undefined;
        if (this.hasItem(key)) {
            previous = this.items[key];
        }
        else {
            this.length++;
        }
        this.items[key] = value;
        return previous;
    }

    this.getItem = function(key) {
        return this.hasItem(key) ? this.items[key] : undefined;
    }

    this.hasItem = function(key) {
        return this.items.hasOwnProperty(key);
    }

    this.removeItem = function(key) {
        if (this.hasItem(key)) {
            previous = this.items[key];
            this.length--;
            delete this.items[key];
            return previous;
        }
        else {
            return undefined;
        }
    }

    this.keys = function() {
        var keys = [];
        for (var k in this.items) {
            if (this.hasItem(k)) {
                keys.push(k);
            }
        }
        return keys;
    }

    this.values = function() {
        var values = [];
        for (var k in this.items) {
            if (this.hasItem(k)) {
                values.push(this.items[k]);
            }
        }
        return values;
    }

    this.each = function(fn) {
        for (var k in this.items) {
            if (this.hasItem(k)) {
                fn(k, this.items[k]);
            }
        }
    }

    this.clear = function() {
        this.items = {}
        this.length = 0;
    }
}


/**
 * setModalLoader
 *
 * Change buttons with loader
 *
 */
function setModalLoader() {
    $(".modal-footer .btn").hide();
    $(".modal-footer .loader").removeClass("hidden");
}


$(document).ready(function() {

    /* Ensures after hide modal content is removed. */
    $('#globalModal').on('hidden.bs.modal', function(e) {
        $(this).removeData('bs.modal');

        // just close modal and reset modal content to default (shows the loader)
        $(this).html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div></div></div>');
    })

});

// call this after every ajax loading
$(document).ajaxComplete(function(event, xhr, settings) {

    // show Tooltips on elements inside the views, which have the class 'tt'
    $('.tt').tooltip({
        html: true,
        container: 'body'
    });

    // show Popovers on elements inside the views, which have the class 'po'
    $('.po').popover({html: true});

    // activate placeholder text for older browsers (specially IE)
    $('input, textarea').placeholder();

});

$('#globalModal').on('shown.bs.modal', function(e) {
    // reduce the standard modal width
    $('.modal-dialog').css('width', '300px');
})


$(document).on('show.bs.modal', '.modal', function(event) {
    $(this).appendTo($('body'));
});
$(document).on('shown.bs.modal', '.modal.in', function(event) {
    setModalsAndBackdropsOrder();
});
$(document).on('hidden.bs.modal', '.modal', function(event) {
    setModalsAndBackdropsOrder();
});


function setModalsAndBackdropsOrder() {
    var modalZIndex = 1040;
    $('.modal.in').each(function(index) {
        var $modal = $(this);
        modalZIndex++;
        $modal.css('zIndex', modalZIndex);
        $modal.next('.modal-backdrop.in').addClass('hidden').css('zIndex', modalZIndex - 1);
    });
    $('.modal.in:visible:last').focus().next('.modal-backdrop.in').removeClass('hidden');
}