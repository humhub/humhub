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
        client.submit(evt, {dataType: 'html'}).then(function (response) {
            var richText = that.getRichtext();
            that.addComment(response.html);
            that.getInput().val('').trigger('autosize.resize');
            richText.$.trigger('clear');
            that.getUpload().reset();
        }).catch(function (err) {
            module.log.error(err, true);
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
    };
    
    Form.prototype.incrementCommentCount = function (count) {
        try {
            var $controls = this.$.closest('.comment-container').siblings('.wall-entry-controls');
            var $commentCount = $controls.find('.comment-count');
            if($commentCount.length) {
                var currentCount = $commentCount.data('count');
                currentCount += count;
                $commentCount.text(' ('+currentCount+')').show();
                $commentCount.data('count', currentCount);
            }
        } catch(e) {
            module.log.error(e);
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
            that.$.find('.comment_edit_content').replaceWith(response.html);
            that.$.find('.comment-cancel-edit-link').show();
            that.$.find('.comment-edit-link').hide();
        }).finally(function () {
            that.loader(false);
        });
    };

    Comment.prototype.getRichtext = function () {
        return Widget.instance(this.$.find('div.humhub-ui-richtext'));
    };

    Comment.prototype.delete = function () {
        this.super('delete', {modal: module.config.modal.delteConfirm}).then(function ($confirm) {
            if ($confirm) {
                module.log.success('success.delete');
            }
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    Comment.prototype.editSubmit = function (evt) {
        var that = this;
        client.submit(evt, {dataType: 'html'}).then(function (response) {
            that.replace(response.html);
            that.highlight();
            that.$.find('.comment-cancel-edit-link').hide();
            that.$.find('.comment-edit-link').show();
            module.log.success('success.saved');
        }).catch(function (err) {
            module.log.error(err, true);
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
            that.$.find('.comment-cancel-edit-link').hide();
            that.$.find('.comment-edit-link').show();
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            that.loader(false);
        });
    };

    Comment.prototype.highlight = function () {
        additions.highlight(this.$.find('.comment-message'));
    };

    Comment.prototype.loader = function ($show) {
        var $loader = this.$.find('.comment-entry-loader');
        if ($show === false) {
            this.$.find('.preferences').show();
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

        this.$.find('.preferences').hide();
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
            var element = $this.find('.preferences');
            if (!loader.is($this.find('.comment-entry-loader'))) {
                element.show();
            }
        });

        $(document).on('mouseout', '.comment .media', function () {
            // find dropdown menu
            var element = $(this).find('.preferences');

            // hide dropdown if it's not open
            if (!element.find('li').hasClass('open')) {
                element.hide();
            }
        });
    };

    var toggleComment = function(evt) {
        var visible = evt.$target.is(':visible');
        evt.$target.slideToggle(undefined, function() {
            evt.$target.find('.humhub-ui-richtext').trigger('focus');
        });
    };

    var scrollActive = function(evt) {
        evt.$trigger.closest('.comment-create-input-group').addClass('scrollActive');
    };

    var scrollInactive = function(evt) {
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
        toggleComment: toggleComment
    });
});