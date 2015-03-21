$.fn.isOnScreen = function(){
    var viewport = {};
    viewport.top = $(window).scrollTop();
    viewport.bottom = viewport.top + $(window).height();
    var bounds = {};
    bounds.top = this.offset().top;
    bounds.bottom = bounds.top + this.outerHeight();
    return ((bounds.top <= viewport.bottom) && (bounds.bottom >= viewport.top));
};

$(
    function () {

        function connect() {
            try {
                socket = new WebSocket("ws://fetfrip.com:8888");
                socket.onmessage = newResponse;
            } catch (e) {
                console.log("could not connect ws");
            }
        
        }
        function newResponse(payload) {
            data = $.parseJSON(payload.data);
            if (data && data.command && data.data) {
                
                // Object to array
                var sorted_comments = [];
                for (var i in data.data) {
                          sorted_comments[i] = data.data[i];
                }

                if (data.command == 'last' && sorted_comments.length) {

                    var area = $('#comments_area_Post_' + data.post_id);
                    var last_comment = area.find('div.media:last');
                    
                    if (last_comment.length > 0) {

                        var cid = (last_comment.attr('id')).match(/[0-9]*$/)[0];
                        for (i in sorted_comments){
                            var comment = $(sorted_comments[i]);
                            console.log(comment);
                            if (comment) {
                                console.log(comment[0]);
                                var id = ($(comment[0]).attr('id')).match(/[0-9]*$/)[0];
                                if (id > cid) {
                                    area.
                                        append($(comment[0]).hide()).
                                        append(comment[1]).
                                        append(comment[3]);
                                    $(comment[0]).slideDown();
                                }
                            }
                        }

                    } else {

                        for (i in sorted_comments){
                            var comment = $(sorted_comments[i]);
                            if (comment) {
                                area.parent().show();
                                area.
                                     append($(comment[0]).hide()).
                                     append(comment[1]).
                                     append(comment[3]);
                                $(comment[0]).slideDown();
                            }
                        }

                    }
                }
            }
        }

        function checkComments() {
            if (typeof document.hidden === 'undefined' || !document.hidden) {
                // we are on the tab or browser does not support visibility API
                // http://www.w3.org/TR/page-visibility/?csw=1#sec-document-interface
                var commands = "";
                console.log(commands);
                $('div.post div.comment').each(function (i,e) {
                    var comments = $(e);
                    var container = comments.closest('div.wall-entry'); 
                    if (container.isOnScreen()) {
                        var post_id = (comments.attr("id")).match(/[0-9]*$/);
                        var id = comments.find("div.content:last");
                        if (id.length) {
                            comment_id = (id.attr('id')).match(/[0-9]*$/)[0];
                        } else {
                            comment_id = -1;
                        }
                        // instead of sending directly merge them
                        // this is used to workaround https://github.com/ghedipunk/PHP-Websockets/issues/22
                        commands = commands + "last|" + post_id +"|" + comment_id + "#";
                    }
                });
                if (commands.length > 0) {
                    console.log(commands);
                    socket.send(commands);
                }
            }
        }
        if (typeof socket === 'undefined') {
            socket = "";
            connect();
            if (typeof check_comment_interval === 'undefined') {
                check_comment_interval = setInterval(checkComments, 5000);
            }
        }
    }
)


