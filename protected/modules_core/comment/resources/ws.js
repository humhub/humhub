$(
    function () {

        function isElementVisible(el) {
            //special bonus for those using jQuery
            if (typeof jQuery === "function" && el instanceof jQuery) {
                el = el[0];
            }

            var rect = el.getBoundingClientRect();
            if (rect.left == 0 && rect.right && rect.bottom == 0 && rect.top == 0) return false;
            return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
                   );
        }


        function connect() {
            try {
                socket = new WebSocket("ws://faraday.mobilada.net:8888");
                socket.onmessage = newResponse;
            } catch (e) {
                console.log("could not connect ws");
            }
        
        }
        function newResponse(payload) {
            data = $.parseJSON(payload.data);
            console.log(data);
            if (data && data.command) {
                if (data.command == 'last' && data.data.length) {
                    var area = $('#comments_area_Post_' + data.post_id);
                    var last_comment = area.find('div.media:last');
                    if (last_comment.length > 0) {
                        var cid = (last_comment.attr('id')).match(/[0-9]*$/)[0];
                        console.log('cid: ' + cid + ' start_after:' + data.startafter);
                        if (data.startafter >= cid) {
                            last_comment.after(data.data);
                        } else {
                            var clear = $("");
                            $(data.data).each(function (i,e) {
                                if (e.tagName == 'div') {
                                    if (($(e).attr(id)).match(/[0-9]*$/)[0] > cid) clear.append(e);
                                }
                            });
                            last_comment.after(clear);
                        }
                    } else {
                        area.append(data.data);
                        area.parent().show();
                    }
                }
            }
        }

        function checkComments() {
            if (typeof document.hidden === 'undefined' || !document.hidden) {
                // we are on the tab or browser does not support visibility API
                // http://www.w3.org/TR/page-visibility/?csw=1#sec-document-interface
                var commands = "";
                $('div.post div.comment').each(function (i,e) {
                    var comments = $(e);
                    var container = comments.closest('div.post'); 
                    if (isElementVisible(container)) {
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
                if (commands.length > 0) socket.send(commands);
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


