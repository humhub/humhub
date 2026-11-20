/**
 * The media viewer: opens the images and videos marked with `data-ui-gallery` in an accessible dialog with zoom,
 * swipe, a video player and a download button. See docs/develop/ui-js-gallery.md for the contract.
 *
 * PhotoSwipe (the global `PhotoSwipe`, PhotoSwipeAsset) is the engine and stays internal: modules use the markup,
 * `open()` and the events `humhub:ui:gallery:opened` / `humhub:ui:gallery:closed` only.
 *
 * @since 1.20 PhotoSwipe instead of blueimp Gallery
 */
humhub.module('ui.gallery', function (module, require, $) {
    var event = require('event');

    // The long side of the size a slide starts with, the bounds of the display version (file module,
    // `imageDisplayMaxWidth`/`-Height`): replaced by the natural size once the image has loaded
    var INITIAL_SIZE = 2048;
    // Room below the image for its caption
    var CAPTION_SPACE = 64;
    var DOWNLOAD_ICON = {
        isCustomSVG: true,
        inner: '<path d="M20.5 14.3 17.1 18V10h-2.2v7.9l-3.4-3.6L10 16l6 6.1 6-6.1ZM23 23H9v2h14Z" id="pswp__icn-download"/>',
        outlineID: 'pswp__icn-download'
    };

    /**
     * The open viewer (if any)
     */
    var current = null;

    /**
     * Whether the open viewer has its own history entry on top, see pushHistory()
     */
    var historyPushed = false;

    var text = function (key, params) {
        var value = (module.config.text || {})[key] || key;
        $.each(params || {}, function (name, param) {
            value = value.split('{' + name + '}').join(param);
        });
        return value;
    };

    var thumbnailOf = function (el) {
        return el.tagName === 'IMG' ? el : el.querySelector('img');
    };

    // Width and height to start with: the aspect ratio of the thumbnail, otherwise 0 (PhotoSwipe takes the viewport)
    var initialSize = function (thumb) {
        if (!thumb || !thumb.naturalWidth || !thumb.naturalHeight) {
            return {width: 0, height: 0};
        }
        var ratio = thumb.naturalWidth / thumb.naturalHeight;
        return ratio >= 1
            ? {width: INITIAL_SIZE, height: Math.round(INITIAL_SIZE / ratio)}
            : {width: Math.round(INITIAL_SIZE * ratio), height: INITIAL_SIZE};
    };

    /**
     * The viewer's item of a `[data-ui-gallery]` element
     *
     * @param {Element} el a link to the file, or an image
     * @returns {{type: string, src: string, download: string, alt: string, caption: string, msrc: (string|undefined),
     *     width: number, height: number, element: Element}}
     */
    var item = function (el) {
        var original = el.getAttribute('href') || el.getAttribute('src') || '';
        var isVideo = (el.getAttribute('type') || '').indexOf('video/') === 0;
        var thumb = isVideo ? null : thumbnailOf(el);
        var size = initialSize(thumb);

        return {
            type: isVideo ? 'video' : 'image',
            src: el.getAttribute('data-ui-gallery-src') || original,
            download: original,
            alt: thumb ? thumb.getAttribute('alt') || '' : '',
            caption: el.getAttribute('title') || '',
            msrc: thumb && thumb !== el ? (thumb.currentSrc || thumb.src) : undefined,
            width: size.width,
            height: size.height,
            element: el
        };
    };

    /**
     * @param {Element[]|NodeList|jQuery} elements
     * @returns {Object[]} the items of the viewer, see item()
     */
    var items = function (elements) {
        return $(elements).toArray().map(item);
    };

    // An image in a link is shown through the link
    var isLinkedImage = function (el) {
        return el.tagName === 'IMG' && !!$(el).closest('a').length;
    };

    // The elements opened together with `el`: the same `data-ui-gallery` value, or its siblings without a value
    var groupOf = function (el) {
        var name = el.getAttribute('data-ui-gallery');
        var $group = name
            ? $('[data-ui-gallery]').filter(function () {
                return this.getAttribute('data-ui-gallery') === name;
            })
            : $(el).parent().children('[data-ui-gallery]');
        return $group.filter(function () {
            return !isLinkedImage(this);
        });
    };

    var errorElement = function (message, url) {
        var $error = $('<div class="pswp__error-msg hh-gallery-error">').text(message);
        if (url) {
            $error.append(' ', $('<a download>').attr('href', url).text(text('download')));
        }
        return $error[0];
    };

    var reducedMotion = function () {
        var appearance = require('ui.appearance');
        return !!(appearance && typeof appearance.reducedMotion === 'function' && appearance.reducedMotion());
    };

    var restoreFocus = function (trigger, root) {
        var focus = require('ui.focus');
        if (focus && typeof focus.restore === 'function') {
            focus.restore(trigger, root);
        } else if (trigger && trigger.isConnected) {
            trigger.focus();
        }
    };

    var isOwnHistory = function () {
        var state = window.history.state;
        return historyPushed && !!state && state.humhubGallery === true;
    };

    // While the viewer is open, Back (the browser's, a gesture, the Android button of the app) closes it and the page
    // stays. The entry keeps the page's state, so pjax takes it for the page.
    var pushHistory = function () {
        if (isOwnHistory()) {
            // A viewer replacing another one keeps its entry
            return;
        }
        try {
            window.history.pushState($.extend({}, window.history.state, {humhubGallery: true}), '', window.location.href);
            historyPushed = true;
        } catch (e) {
            module.log.warn('No history entry for the media viewer', e);
        }
    };

    // Closed with its own controls: the entry goes, so Back leaves the page again
    var popHistory = function () {
        if (isOwnHistory()) {
            window.history.back();
        }
        historyPushed = false;
    };

    var pauseVideos = function (pswp, except) {
        $(pswp.element).find('.hh-gallery-video video').each(function () {
            if (!except || !except.contains(this)) {
                this.pause();
            }
        });
    };

    var videoElement = function (data) {
        var $wrapper = $('<div class="hh-gallery-video">');
        var video = document.createElement('video');
        video.controls = true;
        video.preload = 'metadata';
        video.setAttribute('playsinline', '');
        video.setAttribute('src', data.src);
        if (data.caption) {
            video.setAttribute('aria-label', data.caption);
        }
        // The native controls get the pointer, PhotoSwipe would take it for a swipe
        video.addEventListener('pointerdown', function (evt) {
            evt.stopPropagation();
        });
        video.addEventListener('error', function () {
            $wrapper.empty().append(errorElement(text('videoError'), data.download));
        });
        return $wrapper.append(video)[0];
    };

    // The dialog's name, our controls (download, caption, announcement), slide labels
    var registerUi = function (pswp) {
        pswp.element.setAttribute('aria-modal', 'true');
        pswp.element.setAttribute('aria-label', text('dialog'));

        pswp.ui.registerElement({
            name: 'download',
            order: 8,
            isButton: true,
            tagName: 'a',
            title: text('download'),
            html: DOWNLOAD_ICON,
            onInit: function (el, pswp) {
                el.setAttribute('download', '');
                el.setAttribute('rel', 'noopener');
                var update = function () {
                    el.setAttribute('href', pswp.currSlide ? pswp.currSlide.data.download : pswp.getItemData(pswp.currIndex).download);
                };
                pswp.on('change', update);
                update();
            }
        });

        pswp.ui.registerElement({
            name: 'caption',
            className: 'hh-gallery-caption',
            appendTo: 'root',
            onInit: function (el, pswp) {
                pswp.on('change', function () {
                    var caption = pswp.currSlide ? pswp.currSlide.data.caption : '';
                    el.textContent = caption;
                    el.hidden = !caption;
                });
            }
        });

        pswp.ui.registerElement({
            name: 'status',
            className: 'visually-hidden',
            appendTo: 'root',
            onInit: function (el, pswp) {
                el.setAttribute('aria-live', 'polite');
                // The slide it opens with is not announced: the dialog's name is read then
                var announced = pswp.currIndex;
                pswp.on('change', function () {
                    var slide = pswp.currSlide;
                    if (!slide) {
                        return;
                    }
                    var position = text('position', {index: slide.index + 1, count: pswp.getNumItems()});
                    if (slide.holderElement) {
                        slide.holderElement.setAttribute('aria-label', position);
                    }
                    // Not again on a refresh of the same slide
                    if (announced !== slide.index) {
                        el.textContent = slide.data.caption ? position + ': ' + slide.data.caption : position;
                    }
                    announced = slide.index;
                });
            }
        });
    };

    /**
     * Opens the viewer
     *
     * @param {Element[]|NodeList|jQuery} elements the `[data-ui-gallery]` elements to show, see item()
     * @param {number|Element} index the position or the element to start with
     * @returns {Object|null} the viewer, internal
     */
    var open = function (elements, index) {
        if (typeof window.PhotoSwipe !== 'function') {
            module.log.error('PhotoSwipe is missing');
            return null;
        }
        if (current) {
            // Its destroy leaves the history entry to the new viewer
            var previous = current;
            current = null;
            previous.close();
        }

        var $elements = $(elements);
        var start = typeof index === 'number' ? index : Math.max(0, $elements.index(index));
        var trigger = $elements[start] || null;

        var pswp = new window.PhotoSwipe({
            dataSource: items($elements),
            index: start,
            bgOpacity: 0.92,
            showHideAnimationType: reducedMotion() ? 'none' : 'fade',
            returnFocus: false,
            closeTitle: text('close'),
            zoomTitle: text('zoom'),
            arrowPrevTitle: text('prev'),
            arrowNextTitle: text('next'),
            errorMsg: text('imageError'),
            indexIndicatorSep: ' / ',
            // The one place of the layout: a caption below the image (a side panel would add its width here)
            paddingFn: function (viewportSize, itemData) {
                return {top: 0, right: 0, bottom: itemData && itemData.caption ? CAPTION_SPACE : 0, left: 0};
            }
        });

        pswp.on('uiRegister', function () {
            registerUi(pswp);
        });

        pswp.addFilter('contentErrorElement', function (el, content) {
            return errorElement(text('imageError'), content.data.download);
        });

        pswp.addFilter('isContentZoomable', function (zoomable, content) {
            return content.type === 'video' ? false : zoomable;
        });

        pswp.on('contentLoad', function (evt) {
            if (evt.content.type === 'video') {
                evt.preventDefault();
                evt.content.element = videoElement(evt.content.data);
            }
        });

        // The natural size of a loaded image replaces the one the slide started with
        pswp.on('loadComplete', function (evt) {
            var content = evt.content;
            var img = content && content.element;
            if (!content || content.type !== 'image' || !img || !img.naturalWidth) {
                return;
            }
            if (content.data.width !== img.naturalWidth || content.data.height !== img.naturalHeight) {
                content.data.width = img.naturalWidth;
                content.data.height = img.naturalHeight;
                pswp.refreshSlideContent(content.index);
            }
        });

        pswp.on('keydown', function (evt) {
            var original = evt.originalEvent;
            // Tab goes round the controls of the viewer, the video player included
            if (original.key === 'Tab') {
                var focus = require('ui.focus');
                if (focus && typeof focus.trap === 'function' && focus.trap(original, pswp.element)) {
                    evt.preventDefault();
                }
                return;
            }
            // A key in a video belongs to its player, except Escape
            if (original.key !== 'Escape' && $(original.target).closest('.hh-gallery-video').length) {
                evt.preventDefault();
            }
        });

        pswp.on('change', function () {
            pauseVideos(pswp, pswp.currSlide && pswp.currSlide.container);
        });

        pswp.on('close', function () {
            pauseVideos(pswp);
        });

        pswp.on('bindEvents', function () {
            event.trigger('humhub:ui:gallery:opened');
        });

        pswp.on('destroy', function () {
            restoreFocus(trigger, pswp.element);
            // Not when a new viewer has replaced this one
            if (current === pswp) {
                current = null;
                popHistory();
            }
            event.trigger('humhub:ui:gallery:closed');
        });

        current = pswp;
        pushHistory();
        pswp.init();
        return pswp;
    };

    var init = function () {
        // Back closes an open viewer, its history entry is gone then
        $(window).off('popstate.humhub:ui:gallery').on('popstate.humhub:ui:gallery', function () {
            historyPushed = false;
            if (current) {
                current.close();
            }
        });

        $(document).off('click.humhub:ui:gallery').on('click.humhub:ui:gallery', '[data-ui-gallery]', function (evt) {
            if (isLinkedImage(this)) {
                return;
            }

            evt.preventDefault();
            evt.stopPropagation();
            open(groupOf(this), this);
        });
    };

    module.export({
        init: init,
        open: open,
        items: items,
        sortOrder: 100
    });
});
