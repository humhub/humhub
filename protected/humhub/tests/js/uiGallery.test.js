import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

// A stand-in for the PhotoSwipe core: records what the gallery hands it and dispatches the events in PhotoSwipe's
// order (uiRegister, change, openingAnimationEnd, bindEvents; close, destroy), so the wiring is tested without layout
const instances = [];
class FakePhotoSwipe {
    constructor(options) {
        this.options = options;
        this.handlers = {};
        this.filters = {};
        this.uiElements = {};
        this.currIndex = options.index || 0;
        this.element = document.createElement('div');
        this.element.className = 'pswp';
        this.element.setAttribute('role', 'dialog');
        this.element.tabIndex = -1;
        this.bar = document.createElement('div');
        this.element.appendChild(this.bar);
        this.holders = [0, 1, 2].map(() => {
            const el = document.createElement('div');
            el.setAttribute('role', 'group');
            this.element.appendChild(el);
            return el;
        });
        this.ui = {
            registerElement: (def) => {
                const el = document.createElement(def.tagName || (def.isButton ? 'button' : 'div'));
                el.className = def.className || 'pswp__' + def.name;
                if (def.title) {
                    el.setAttribute('aria-label', def.ariaLabel || def.title);
                }
                (def.appendTo === 'root' ? this.element : this.bar).appendChild(el);
                if (def.onInit) {
                    def.onInit(el, this);
                }
                this.uiElements[def.name] = el;
            },
        };
        instances.push(this);
    }

    on(name, fn) {
        (this.handlers[name] ||= []).push(fn);
    }

    addFilter(name, fn) {
        (this.filters[name] ||= []).push(fn);
    }

    applyFilters(name, value, ...args) {
        return (this.filters[name] || []).reduce((result, fn) => fn(result, ...args), value);
    }

    dispatch(name, details = {}) {
        const event = {
            ...details,
            defaultPrevented: false,
            preventDefault() {
                this.defaultPrevented = true;
            },
        };
        (this.handlers[name] || []).forEach((fn) => fn(event));
        return event;
    }

    getNumItems() {
        return this.options.dataSource.length;
    }

    getItemData(index) {
        return this.options.dataSource[index];
    }

    get currSlide() {
        return { index: this.currIndex, data: this.getItemData(this.currIndex), holderElement: this.holders[1], container: this.holders[1] };
    }

    init() {
        document.body.appendChild(this.element);
        this.dispatch('uiRegister');
        this.dispatch('change');
        this.dispatch('openingAnimationEnd');
        this.element.focus();
        this.dispatch('bindEvents');
    }

    goTo(index) {
        this.currIndex = index;
        this.dispatch('change');
    }

    close() {
        this.dispatch('close');
        this.destroy();
    }

    destroy() {
        this.dispatch('destroy');
        this.element.remove();
    }

    refreshSlideContent(index) {
        (this.refreshed ||= []).push(index);
    }
}
globalThis.PhotoSwipe = FakePhotoSwipe;

// jsdom has no layout: an element counts as shown unless it or an ancestor is `hidden` (as in uiFocus.test.js)
Element.prototype.checkVisibility = function () {
    for (let el = this; el; el = el.parentElement) {
        if (el.hidden || el.style.display === 'none') {
            return false;
        }
    }
    return true;
};

await import('../../resources/js/humhub/humhub.ui.focus.js');
await import('../../resources/js/humhub/humhub.ui.gallery.js');
const gallery = globalThis.humhub.modules['ui.gallery'];
const events = globalThis.humhubStubs.event;

const TEXTS = {
    dialog: 'Media viewer',
    close: 'Close',
    prev: 'Previous',
    next: 'Next',
    zoom: 'Zoom',
    download: 'Download',
    position: '{index} of {count}',
    imageError: 'The image cannot be loaded.',
    videoError: 'The video cannot be played in this browser.',
};

const html = (markup) => {
    const doc = new DOMParser().parseFromString(markup, 'text/html');
    document.body.replaceChildren(...doc.body.childNodes);
};
const byId = (id) => document.getElementById(id);
const group = () => document.querySelectorAll('[data-ui-gallery="g"]');
const click = (el) => {
    const event = new MouseEvent('click', { bubbles: true, cancelable: true });
    el.dispatchEvent(event);
    return event;
};
const viewer = () => instances[instances.length - 1];
// The popstate of a history traversal, which jsdom runs on a timer like a browser
const popped = () => new Promise((resolve) => window.addEventListener('popstate', resolve, { once: true }));
const groupMarkup = `
    <a id="a1" href="/file/1.jpg" data-ui-gallery="g" data-ui-gallery-src="/file/1-display.jpg" title="First">
        <img src="/file/1-preview.png" alt="A cat">
    </a>
    <a id="a2" href="/file/2.png" data-ui-gallery="g"><img src="/file/2-preview.png" alt=""></a>
    <a id="a3" href="/file/3.mp4" type="video/mp4" data-ui-gallery="g" title="Clip"><video></video></a>
    <a id="other" href="/file/9.jpg" data-ui-gallery="other"><img src="/file/9-preview.png" alt="Elsewhere"></a>`;

describe('ui.gallery', () => {
    beforeEach(() => {
        Object.assign(gallery.config, { text: TEXTS });
        instances.length = 0;
        events._handlers.clear();
        delete globalThis.humhub.modules['ui.appearance'];
    });

    afterEach(async () => {
        // A viewer closing with its own controls goes back in the history: wait for it, it must not close the next one
        const back = window.history.state?.humhubGallery ? popped() : null;
        instances.forEach((pswp) => pswp.element.isConnected && pswp.destroy());
        await back;
        document.body.replaceChildren();
    });

    describe('items()', () => {
        it('describes a link: shown and download url, alt, caption, placeholder', () => {
            html(groupMarkup);
            const [first, second] = gallery.items([byId('a1'), byId('a2')]);

            expect(first).toMatchObject({
                type: 'image',
                src: '/file/1-display.jpg',
                download: '/file/1.jpg',
                alt: 'A cat',
                caption: 'First',
                msrc: expect.stringContaining('/file/1-preview.png'),
            });
            expect(second).toMatchObject({ src: '/file/2.png', download: '/file/2.png', alt: '', caption: '' });
        });

        it('takes a link with a video type as a video', () => {
            html(groupMarkup);
            const [video] = gallery.items([byId('a3')]);

            expect(video).toMatchObject({ type: 'video', src: '/file/3.mp4', download: '/file/3.mp4', caption: 'Clip' });
            expect(video.msrc).toBeUndefined();
        });

        it('takes an image in rich text by its source', () => {
            html('<img id="i" src="/file/5.jpg" alt="Map" title="Our map" data-ui-gallery="text">');
            const [image] = gallery.items([byId('i')]);

            expect(image).toMatchObject({ type: 'image', src: '/file/5.jpg', download: '/file/5.jpg', alt: 'Map', caption: 'Our map' });
        });

        it('starts with the aspect ratio of the thumbnail, the long side 2048', () => {
            html('<a id="a" href="/x.jpg" data-ui-gallery="g"><img id="t" src="/x-preview.png" alt=""></a>');
            Object.defineProperty(byId('t'), 'naturalWidth', { value: 400 });
            Object.defineProperty(byId('t'), 'naturalHeight', { value: 300 });

            expect(gallery.items([byId('a')])[0]).toMatchObject({ width: 2048, height: 1536 });
        });
    });

    describe('opening', () => {
        it('opens the group of a clicked link at its position', () => {
            html(groupMarkup);
            const event = click(byId('a2'));

            expect(event.defaultPrevented).toBe(true);
            expect(viewer().options.index).toBe(1);
            expect(viewer().options.dataSource.map((item) => item.download)).toEqual(['/file/1.jpg', '/file/2.png', '/file/3.mp4']);
        });

        it('opens the siblings when the group has no name', () => {
            html('<div><a id="x" href="/a.jpg" data-ui-gallery=""><img src="/a-p.png" alt=""></a><a href="/b.jpg" data-ui-gallery=""><img src="/b-p.png" alt=""></a></div>'
                + '<div><a href="/c.jpg" data-ui-gallery=""><img src="/c-p.png" alt=""></a></div>');
            click(byId('x'));

            expect(viewer().options.dataSource.map((item) => item.download)).toEqual(['/a.jpg', '/b.jpg']);
        });

        it('leaves an image in a link to the link', () => {
            html('<a href="/a.jpg" data-ui-gallery="g"><img id="i" src="/a.jpg" alt="" data-ui-gallery="g"></a>');
            click(byId('i'));

            expect(instances).toHaveLength(1);
            expect(viewer().options.dataSource).toHaveLength(1);
        });

        it('is a named modal dialog with translated controls', () => {
            html(groupMarkup);
            gallery.open(group(), 0);

            expect(viewer().element.getAttribute('aria-modal')).toBe('true');
            expect(viewer().element.getAttribute('aria-label')).toBe('Media viewer');
            expect(viewer().options).toMatchObject({
                closeTitle: 'Close',
                arrowPrevTitle: 'Previous',
                arrowNextTitle: 'Next',
                zoomTitle: 'Zoom',
                errorMsg: 'The image cannot be loaded.',
                returnFocus: false,
                showHideAnimationType: 'fade',
            });
        });

        it('opens without animation for reduced motion', () => {
            globalThis.humhub.modules['ui.appearance'] = { reducedMotion: () => true };
            html(groupMarkup);
            gallery.open(group(), 0);

            expect(viewer().options.showHideAnimationType).toBe('none');
        });

        it('triggers opened once the dialog has the focus, closed when it is gone', () => {
            const seen = [];
            events.on('humhub:ui:gallery:opened', () => seen.push(['opened', document.activeElement === viewer().element]));
            events.on('humhub:ui:gallery:closed', () => seen.push(['closed']));
            html(groupMarkup);
            gallery.open(group(), 0);
            viewer().close();

            expect(seen).toEqual([['opened', true], ['closed']]);
        });

        it('opens at the given element', () => {
            html(groupMarkup);
            gallery.open(group(), group()[2]);

            expect(viewer().options.index).toBe(2);
        });
    });

    describe('slides', () => {
        it('names the slide by its position and announces a change with the caption', () => {
            html(groupMarkup);
            gallery.open(group(), 0);
            const status = viewer().element.querySelector('[aria-live="polite"]');

            expect(viewer().currSlide.holderElement.getAttribute('aria-label')).toBe('1 of 3');
            expect(status.textContent).toBe('');

            viewer().goTo(2);
            expect(viewer().currSlide.holderElement.getAttribute('aria-label')).toBe('3 of 3');
            expect(status.textContent).toBe('3 of 3: Clip');
        });

        it('shows the caption of the current slide', () => {
            html(groupMarkup);
            gallery.open(group(), 0);
            const caption = viewer().uiElements.caption;

            expect(caption.textContent).toBe('First');
            expect(caption.hidden).toBe(false);

            viewer().goTo(1);
            expect(caption.hidden).toBe(true);
        });

        it('reserves room below the image for a caption', () => {
            html(groupMarkup);
            gallery.open(group(), 0);
            const padding = viewer().options.paddingFn;

            expect(padding({ x: 1000, y: 800 }, { caption: 'First' }).bottom).toBeGreaterThan(padding({ x: 1000, y: 800 }, { caption: '' }).bottom);
        });

        it('downloads the original of the current slide', () => {
            html(groupMarkup);
            gallery.open(group(), 0);
            const download = viewer().uiElements.download;

            expect(download.tagName).toBe('A');
            expect(download.getAttribute('aria-label')).toBe('Download');
            expect(download.hasAttribute('download')).toBe(true);
            expect(download.getAttribute('href')).toBe('/file/1.jpg');

            viewer().goTo(1);
            expect(download.getAttribute('href')).toBe('/file/2.png');
        });

        it('offers the download when an image cannot be loaded', () => {
            html(groupMarkup);
            gallery.open(group(), 0);
            const error = viewer().applyFilters('contentErrorElement', document.createElement('div'), { data: viewer().getItemData(0) });

            expect(error.textContent).toContain('The image cannot be loaded.');
            expect(error.querySelector('a[download]').getAttribute('href')).toBe('/file/1.jpg');
        });

        it('takes the natural size of a loaded image', () => {
            html(groupMarkup);
            gallery.open(group(), 0);
            const img = document.createElement('img');
            Object.defineProperty(img, 'naturalWidth', { value: 1200 });
            Object.defineProperty(img, 'naturalHeight', { value: 900 });
            const data = viewer().getItemData(0);
            const loaded = () => viewer().dispatch('loadComplete', { content: { type: 'image', element: img, data, index: 0 } });

            loaded();
            expect(data).toMatchObject({ width: 1200, height: 900 });
            expect(viewer().refreshed).toEqual([0]);

            loaded();
            expect(viewer().refreshed).toEqual([0]);
        });
    });

    describe('videos', () => {
        const loadVideo = () => {
            const content = { type: 'video', data: viewer().getItemData(2), index: 2 };
            const event = viewer().dispatch('contentLoad', { content });
            // As PhotoSwipe appends custom content: only an element without a parent
            if (!content.element.parentNode) {
                viewer().currSlide.container.appendChild(content.element);
            }
            return { content, event, video: content.element.querySelector('video') };
        };

        it('plays a video in the native player', () => {
            html(groupMarkup);
            gallery.open(group(), 2);
            const { content, event, video } = loadVideo();

            expect(event.defaultPrevented).toBe(true);
            expect(content.element.classList.contains('hh-gallery-video')).toBe(true);
            expect(content.element.parentNode).toBe(viewer().currSlide.container);
            expect(video.controls).toBe(true);
            expect(video.hasAttribute('playsinline')).toBe(true);
            expect(video.getAttribute('src')).toBe('/file/3.mp4');
            expect(viewer().applyFilters('isContentZoomable', true, content)).toBe(false);
        });

        it('pauses a video when its slide is left or the viewer closes', () => {
            html(groupMarkup);
            gallery.open(group(), 2);
            const { video } = loadVideo();
            const pause = vi.spyOn(video, 'pause').mockImplementation(() => {});

            viewer().holders[1].removeChild(video.parentElement);
            viewer().holders[2].appendChild(video.parentElement);
            viewer().goTo(0);
            expect(pause).toHaveBeenCalledTimes(1);

            viewer().close();
            expect(pause).toHaveBeenCalledTimes(2);
        });

        it('leaves the keys in a video to the player, but Escape', () => {
            html(groupMarkup);
            gallery.open(group(), 2);
            const { video } = loadVideo();
            const key = (k) => viewer().dispatch('keydown', { originalEvent: { key: k, target: video } }).defaultPrevented;

            expect(key('ArrowLeft')).toBe(true);
            expect(key(' ')).toBe(true);
            expect(key('Escape')).toBe(false);
        });

        it('offers the download when the browser cannot play the video', () => {
            html(groupMarkup);
            gallery.open(group(), 2);
            const { content, video } = loadVideo();
            video.dispatchEvent(new Event('error'));

            expect(content.element.textContent).toContain('The video cannot be played in this browser.');
            expect(content.element.querySelector('a[download]').getAttribute('href')).toBe('/file/3.mp4');
        });
    });

    describe('keyboard', () => {
        it('goes round the controls of the viewer with Tab', () => {
            html(groupMarkup);
            gallery.open(group(), 0);
            const close = document.createElement('button');
            viewer().bar.appendChild(close);
            close.focus();
            const tab = new KeyboardEvent('keydown', { key: 'Tab', cancelable: true });

            expect(viewer().dispatch('keydown', { originalEvent: tab }).defaultPrevented).toBe(true);
            expect(document.activeElement).toBe(viewer().uiElements.download);
        });
    });

    describe('closing', () => {
        it('hands the focus back to the clicked element', () => {
            html(groupMarkup);
            const link = byId('a2');
            link.focus();
            click(link);
            expect(document.activeElement).toBe(viewer().element);

            viewer().close();
            expect(document.activeElement).toBe(link);
        });
    });

    describe('history', () => {
        it('closes with Back, the page stays', async () => {
            html(groupMarkup);
            click(byId('a1'));

            expect(window.history.state.humhubGallery).toBe(true);

            const back = popped();
            window.history.back();
            await back;

            expect(viewer().element.isConnected).toBe(false);
            expect(window.history.state?.humhubGallery).toBeUndefined();
            expect(byId('a1').isConnected).toBe(true);
        });

        it('keeps the state of the page for pjax', () => {
            window.history.replaceState({ id: 7, url: window.location.href, container: '#layout-content' }, '');
            html(groupMarkup);
            click(byId('a1'));

            expect(window.history.state).toMatchObject({ id: 7, container: '#layout-content', humhubGallery: true });
        });

        it('removes its entry when closed with its own controls', async () => {
            html(groupMarkup);
            click(byId('a1'));

            const back = popped();
            viewer().close();
            await back;

            expect(window.history.state?.humhubGallery).toBeUndefined();
        });

        it('keeps the one entry when a viewer replaces another', () => {
            html(groupMarkup);
            const back = vi.spyOn(window.history, 'back');
            click(byId('a1'));
            const length = window.history.length;
            const first = viewer();

            click(byId('other'));

            expect(first.element.isConnected).toBe(false);
            expect(viewer()).not.toBe(first);
            expect(window.history.length).toBe(length);
            expect(back).not.toHaveBeenCalled();
            back.mockRestore();
        });
    });
});
