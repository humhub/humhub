humhub.module('comment', function (module, require, $) {
    var Content = require('content').Content;
    var Widget = require('ui.widget').Widget;
    var object = require('util').object;
    var client = require('client');
    var loader = require('ui.loader');
    var additions = require('ui.additions');

    var Form = Widget.extend();

    Form.prototype.submit = function (evt) {
        var that = this;
        client.submit(evt, {dataType: 'html'}).status({
            200: function (response) {
                var richText = that.getRichtext();
                that.addComment(response.html);
                that.getInput().val('').trigger('autosize.resize');
                richText.$.trigger('clear');
                that.getUpload().reset();
                that.$.find('.form-group').removeClass('has-error');
                that.$.find('.help-block-error').html('');
            },
            400: function (response) {
                that.replace(response.html);
            }
        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    Form.prototype.getRichtext = function () {
        return Widget.closest(this.$.find('div.humhub-ui-richtext'));
    };

    Form.prototype.addComment = function (html) {
        var $html = $(html);

        // Filter out all script/links and text nodes
        var $elements = $html.not('script, link').filter(function () {
            return this.nodeType === 1; // filter out text nodes
        });

        // We use opacity because some additions require the actual size of the elements.
        $elements.css('opacity', 0);

        // call insert callback
        this.getCommentsContainer().append($html);
        this.incrementCommentCount(1);

        // apply additions to elements and fade them in.
        additions.applyTo($elements);

        $elements.hide().css('opacity', 1).fadeIn('fast');

        this.$.find('hr').show();
    };

    Form.prototype.incrementCommentCount = function (count) {
        try {
            // First check if this is a sub comment form
            var $root = this.$.closest('[data-action-component="comment.Comment"]');
            if (!$root.length) {
                $root = this.$.closest('.stream-entry-addons');
            }

            if (!$root.length) {
                return;
            }

            var $controls = $root.find('.wall-entry-controls:first');
            if (!$controls.length) {
                return;
            }

            var $commentCount = $controls.find('.comment-count');
            if ($commentCount.length) {
                var currentCount = $commentCount.data('count');
                currentCount += count;
                $commentCount.text(' (' + currentCount + ')').show();
                $commentCount.data('count', currentCount);
            }
        } catch (e) {
            module.log.error(e, false);
        }
    };

    Form.prototype.getUpload = function () {
        return Widget.instance(this.$.find('.main_comment_upload'));
    };

    Form.prototype.getCommentsContainer = function () {
        return this.$.siblings('.comment');
    };

    Form.prototype.getInput = function () {
        return this.$.find('textarea');
    };

    var Comment = Content.extend(function (node) {
        Content.call(this, node);
        additions.observe(this.$);
    });

    Comment.prototype.edit = function (evt) {
        this.loader();
        var that = this;
        client.post(evt, {dataType: 'html'}).then(function (response) {
            that.setEditContent(response.html);
        }).finally(function () {
            that.loader(false);
        });
    };

    Comment.prototype.setEditContent = function (html) {
        this.$.find('.comment_edit_content:first,.content_edit:first').replaceWith(html);
        this.$.find('.comment-cancel-edit-link:first').show();
        this.$.find('.comment-edit-link:first').hide();
    };

    Comment.prototype.getRichtext = function () {
        return Widget.instance(this.$.find('div.humhub-ui-richtext:first'));
    };

    Comment.prototype.delete = function (evt) {
        var $form = this.$.parent().siblings('.comment_create');
        var hideHr = !this.isNestedComment() && $form.length && !this.$.siblings('.media').length;

        this.$.data('content-delete-url', evt.$trigger.data('content-delete-url'));

        this.super('delete', {modal: module.config.modal.delteConfirm}).then(function ($confirm) {
            if ($confirm) {
                module.log.success('success.delete');
                if (hideHr) {
                    $form.find('hr').hide();
                }
            }
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    Comment.prototype.adminDelete = function (evt) {
        var $form = this.$.parent().siblings('.comment_create');
        var hideHr = !this.isNestedComment() && $form.length && !this.$.siblings('.media').length;

        this.$.data('content-delete-url', evt.$trigger.data('content-delete-url'));
        this.$.data('admin-delete-modal-url', evt.$trigger.data('admin-delete-modal-url'));

        this.super('adminDelete').then(function ($confirm) {
            if ($confirm) {
                module.log.success('success.delete');
                if (hideHr) {
                    $form.find('hr').hide();
                }
            }
        }).catch(function (err) {
            module.log.error(err, true);
        });
    }

    Comment.prototype.isNestedComment = function () {
        return this.$.closest('.nested-comments-root').length !== 0;
    };

    Comment.prototype.editSubmit = function (evt) {
        var that = this;
        client.submit(evt, {dataType: 'html'}).status({
            200: function (response) {
                that.replace(response.html);
                that.highlight();
                that.$.find('.comment-cancel-edit-link:first').hide();
                that.$.find('.comment-edit-link:first').show();
            },
            400: function (response) {
                that.setEditContent(response.html);
            }
        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    Comment.prototype.replace = function (content) {
        var id = this.$.attr('id');
        this.$.replaceWith(content);
        this.$ = $('#' + id);
        additions.observe(this.$, true);
    };

    Comment.prototype.cancelEdit = function (evt) {
        var that = this;
        this.loader();
        client.html(evt).then(function (response) {
            that.replace(response.html);
            that.$.find('.comment-cancel-edit-link:first').hide();
            that.$.find('.comment-edit-link:first').show();
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            that.loader(false);
        });
    };

    Comment.prototype.highlight = function () {
        additions.highlight(this.$.find('.comment-message:first'));
    };

    Comment.prototype.loader = function ($show) {
        var $loader = this.$.find('.comment-entry-loader:first');
        if ($show === false) {
            this.$.find('.preferences:first').show();
            loader.reset($loader);
            return;
        }

        loader.set($loader, {
            'size': '8px',
            'css': {
                padding: '2px',
                width: '60px'
            }
        });

        this.$.find('.preferences:first').hide();
    };

    Comment.prototype.showBlocked = function (evt) {
        var that = this;
        that.loader();
        client.html(evt).then(function (response) {
            that.replace(response.html);
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            that.loader(false);
        });
    };

    var showAll = function (evt) {
        client.post(evt, {dataType: 'html'}).then(function (response) {
            var $container = evt.$trigger.parent();
            $container.html(response.html);
            additions.applyTo($container);
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    var showMore = function (evt) {
        loader.set(evt.$trigger, {
            'size': '8px',
            'css': {
                padding: '2px',
                width: '60px'
            }
        });
        client.post(evt, {dataType: 'html'}).then(function (response) {
            var $container = evt.$trigger.closest('.comment');
            var $html = $(response.html);
            $container.prepend($html);
            evt.$trigger.closest('.showMore').remove();
            additions.applyTo($html);
        }).catch(function (err) {
            module.log.error(err, true);
            loader.unset(evt.$trigger);
        });
    };

    var init = function () {
        $(document).on('mouseover', '.comment .media', function () {
            var $this = $(this);
            var element = $this.find('.preferences:first');
            if (!loader.is($this.find('.comment-entry-loader'))) {
                element.show();
            }
        });
        $(document).on('mouseout', '.comment .media', function () {
            // find dropdown menu
            var element = $(this).find('.preferences:first');

            // hide dropdown if it's not open
            if (!element.find('li').hasClass('open')) {
                element.hide();
            }
        });
    };

    function toggleComment(target, isSlideToggle) {
        var visible = target.is(':visible');

        // Comments are shown but form is not visible yet --> Toggle form only
        if (visible && !target.children('.comment_create').is(':visible')) {
            target.children('.comment_create').slideToggle(undefined, function () {
                target.find('.humhub-ui-richtext').trigger('focus');
            });
            return;
        }

        var $form = target.children('.comment_create');

        if (!target.find('.comment .media').length && !target.closest('[data-action-component="comment.Comment"]').length) {
            $form.find('hr').hide();
        }

        $form.show();

        if (isSlideToggle) {
            target.slideToggle();
        }

        if(!visible) {
            target.find('.humhub-ui-richtext').trigger('focus');
        }
    }

    var toggleCommentHandler = function (evt) {
        var target;

        // Only one level of subcomments allowed. If Replay button is pressed under second level of comments then toggle parent first level.
        if (evt.$target.parents('.nested-comments-root').length < 2) {
            //toggle child comment
            target = evt.$target;
            toggleComment(target, true);
        } else {
            //toggle parent comment
            target = evt.$target.closest('.comment').closest('.comment-container');
            toggleComment(target, false);
            var richtext = Widget.instance(target.find('.ProsemirrorEditor:last'));
            var mentioning = require('ui.richtext.prosemirror').buildMentioning(evt.$target.closest('.media').find('.media-heading a'));
            richtext.editor.init(mentioning);
            richtext.$.trigger('focus');
        }
    };

    var scrollActive = function (evt) {
        evt.$trigger.closest('.comment-create-input-group').addClass('scrollActive');
    };

    var scrollInactive = function (evt) {
        evt.$trigger.closest('.comment-create-input-group').removeClass('scrollActive');
    };

    module.export({
        init: init,
        Comment: Comment,
        Form: Form,
        scrollActive: scrollActive,
        scrollInactive: scrollInactive,
        showAll: showAll,
        showMore: showMore,
        toggleComment: toggleCommentHandler
    });
});
