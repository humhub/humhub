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
            htmlString = htmlString.replace(match[0], "");

        } else {
            // Let Script Tag
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

    this.setItem = function (key, value) {
        var previous = undefined;
        if (this.hasItem(key)) {
            previous = this.items[key];
        } else {
            this.length++;
        }
        this.items[key] = value;
        return previous;
    }

    this.getItem = function (key) {
        return this.hasItem(key) ? this.items[key] : undefined;
    }

    this.hasItem = function (key) {
        return this.items.hasOwnProperty(key);
    }

    this.removeItem = function (key) {
        if (this.hasItem(key)) {
            previous = this.items[key];
            this.length--;
            delete this.items[key];
            return previous;
        } else {
            return undefined;
        }
    }

    this.keys = function () {
        var keys = [];
        for (var k in this.items) {
            if (this.hasItem(k)) {
                keys.push(k);
            }
        }
        return keys;
    }

    this.values = function () {
        var values = [];
        for (var k in this.items) {
            if (this.hasItem(k)) {
                values.push(this.items[k]);
            }
        }
        return values;
    }

    this.each = function (fn) {
        for (var k in this.items) {
            if (this.hasItem(k)) {
                fn(k, this.items[k]);
            }
        }
    }

    this.clear = function () {
        this.items = {}
        this.length = 0;
    }
}


/**
 * To allow other frameworks to overlay focusable nodes over an active modal we have
 * to explicitly allow ith within this overwritten function.
 *
 */
$.fn.modal.Constructor.prototype.enforceFocus = function () {
    var that = this;
    $(document).on('focusin.modal', function (e) {
        var $target = $(e.target);
        if ($target.hasClass('select2-input') || $target.hasClass('select2-search__field') || $target.hasClass('hexInput')) {
            return true;
        }

        var $parent = $(e.target.parentNode);
        if ($parent.hasClass('cke_dialog_ui_input_select') || $parent.hasClass('cke_dialog_ui_input_text')) {
            return true;
        }

        // Allow stacking of modals
        if ($target.closest('.modal.in').length) {
            return true;
        }

        if (that.$element[0] !== e.target && !that.$element.has(e.target).length) {
            that.$element.focus();
        }
    });
};

function setModalLoader() {
    $(".modal-footer .btn").hide();
    $(".modal-footer .loader").removeClass("hidden");
}


$(document).ready(function () {

    /* Ensures after hide modal content is removed. */
    $('#globalModal').on('hidden.bs.modal', function (e) {
        $(this).removeData('bs.modal');

        // just close modal and reset modal content to default (shows the loader)
        $(this).html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body">\n\
<div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div></div></div>');
    });

    // set Modal handler to all modal links
    setModalHandler();

    initPlugins();

    $(document).on('click', 'a[data-ui-loader], button[data-ui-loader]', function (evt) {
        var $this = $(this);

        var $loader = $this.find('.loader').length > 0;

        // Prevent multiple mouse clicks, if originalEvent is present its a real mouse event otherwise its script triggered
        // This is a workaround since yii version 2.0.10 changed the activeForm submission from $form.submit() to data.submitObject.trigger("click");
        // which triggers this handler twice. Here we get sure not to block the script triggered submission.
        if ($loader && evt.originalEvent) {
            return false;
        } else if ($loader) {
            return;
        }

        //Adopt current color for the loader animation
        var color = $this.css('color') || '#ffffff';
        $loader = $('<span class="loader"><span class="sk-spinner sk-spinner-three-bounce"><span class="sk-bounce1"></span><span class="sk-bounce2"></span><span class="sk-bounce3"></span></span></span>');

        //Align bouncer animation color and size
        $loader.find('.sk-bounce1, .sk-bounce2, .sk-bounce3')
                .addClass('disabled')
                .css({'background-color': color, 'width': '10px', 'height': '10px'});

        //The loader does have some margin we have to hide
        $this.css('overflow', 'hidden');
        $this.addClass('disabled');

        //Prevent the container from resizing
        $this.css('min-width', this.getBoundingClientRect().width);
        $this.data('text', $this.text());
        $this.html($loader);
    });

    $(document).on('afterValidate', function (evt, messages, errors) {
        if (errors.length) {
            $('[data-ui-loader]').each(function () {
                var $this = $(this);
                if ($this.find('.loader').length) {
                    $this.html($this.data('text'));
                    $this.removeClass('disabled');
                }
            });
        }
    });

    $('input').on('invalid', function () {
        $('[data-ui-loader]').each(function () {
            var $this = $(this);
            if ($this.find('.loader').length) {
                $this.html($this.data('text'));
                $this.removeClass('disabled');
            }
        });
    });

});

function setModalHandler() {

    // unbind all previously-attached events
    $("a[data-target='#globalModal']").unbind();

    $(document).off('click.humhub');
    $(document).on('click.humhub', "a[data-target='#globalModal']", function (ev) {
        ev.preventDefault();
        var options = {
            'show': true,
            'backdrop': $(this).data('backdrop')
        }
        $("#globalModal").modal(options);
        var target = $(this).attr("href");

        // load the url and show modal on success
        $("#globalModal").load(target, function () {
            // animate options
        });
    });
}

function initPlugins() {

    // show Tooltips on elements inside the views, which have the class 'tt'
    $('.tt').tooltip({
        html: false,
        container: 'body'
    });

    // show Popovers on elements inside the views, which have the class 'po'
    $('.po').popover({html: true});

    // activate placeholder text for older browsers (specially IE)
    $('input, textarea').placeholder();

    // Replace the standard checkbox and radio buttons
    $('body').find(':checkbox, :radio').flatelements();

    $('a[data-loader="modal"], button[data-loader="modal"]').loader();

}

// call this after every ajax loading
$(document).ajaxComplete(function (event, xhr, settings) {

    initPlugins();

    // set Modal handler to all modal links
    setModalHandler();

});

$('#globalModal').on('shown.bs.modal', function (e) {
    // reduce the standard modal width
    $('.modal-dialog').css('width', '300px');
})


$(document).on('show.bs.modal', '.modal', function (event) {
    $(this).appendTo($('body'));
});
$(document).on('shown.bs.modal', '.modal.in', function (event) {
    setModalsAndBackdropsOrder();
});
$(document).on('hidden.bs.modal', '.modal', function (event) {
    setModalsAndBackdropsOrder();
});



function setModalsAndBackdropsOrder() {
    var modalZIndex = 1040;
    $('.modal.in').each(function (index) {
        var $modal = $(this);
        modalZIndex++;
        $modal.css('zIndex', modalZIndex);
        $modal.next('.modal-backdrop.in').addClass('hidden').css('zIndex', modalZIndex - 1);
    });
    $('.modal.in:visible:last').focus().next('.modal-backdrop.in').removeClass('hidden');
}

//////////////////////////////////////////////////////////////////////////
////////////////////////// TIME-FORMATTING ///////////////////////////////
//////////////////////////////////////////////////////////////////////////


// Get favourites
$.fn.format = function (options) {

    // get the id from the invoking element
    var _id = $(this).attr("id");


    //*************** Private Functions ****************//

    var setTimeFormat = function () {

        // save value from textinput into a variable
        var _value = $("#" + _id).val();

        if (_value != "") {

            // by this type, set the value to hours until the value is 23
            if (options.type == "daytime") {

                // set only hours
                if (_value <= 23) {

                    // find the right value
                    for (var j = 1; j < 24; j++) {
                        if (_value == j) {

                            // set the right time format
                            _value = j + ":00";

                        }
                    }
                }

                // set only minutes
                if (_value > 23 && _value <= 59) {
                    _value = "0:" + _value;
                }

            }


            // divide in hours and minutes by a string length of 3
            if (_value >= 60 && _value < 1000) {
                _value = _value.substr(0, 1) + ":" + _value.substr(1, 2);
            }

            // divide in hours and minutes by a string length of 4
            if (_value >= 1000 && _value < 10000) {
                _value = _value.substr(0, 2) + ":" + _value.substr(2, 3);
            }

            // if the value is "0" and that isn't allowed, empty the string
            if (options.zero == false && _value == "0:00") {
                _value = "";
            }

            var str = _value;
            var res = str.split(":");

            if (_value.length < 5) {
                if (res[0] < 10) {
                    _value = "0" + res[0] + ":" + res[1];
                }
            }


            // provide the well formated value
            return _value;

        }
    }


    //*************** Event Handler ****************//

    $("#" + _id).focusout(function () {

        // set time format
        if (options.type == "euro") {
            $("#" + _id).val(setEuroFormat());
        } else {
            $("#" + _id).val(setTimeFormat());
        }

    })

}

function htmlEncode(value) {
    //create a in-memory div, set it's inner text(which jQuery automatically encodes)
    //then grab the encoded contents back out.  The div never exists on the page.
    return $('<div/>').text(value).html();
}

function htmlDecode(value) {
    return $('<div/>').html(value).text();
}









