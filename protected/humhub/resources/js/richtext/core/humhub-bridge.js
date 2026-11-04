// TODO: enable global default config e.g. for emoji, locale, etc

export const isHumhub = () => {
    return humhub && humhub.modules;
}

export const showSuccessNotify = (message) => {
    if (isHumhub) {
        humhub.modules.ui.status.success(message);
    }
}

export function onDocumentReady(callback) {
    if (!window.humhub) {
        return $(document).ready(() => {
            callback.call(null, false);
        });
    }

    humhub.event.on('humhub:ready', callback);
}

export function getEmojiConfig() {
    if (!window.humhub || !window.humhub.config) {
        return {};
    }

    return humhub.config.get('ui.richtext.prosemirror', 'emoji', {twemoji: {}});
}

export function isSmallView() {
    if (!window.humhub) {
        return getClientWidth() <= 767;
    }

    return isHumhub() ? humhub.modules.ui.view.isSmall() : null;
}

const getClientWidth = () => {
    return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
};

export function getUserLocale() {
    if (!window.humhub) {
        return (navigator.languages && navigator.languages.length) ? navigator.languages[0] : navigator.language;
    }

    return isHumhub() ? humhub.modules.user.config.locale.split("-")[0] : null;
}

export function filterFileUrl(url, mode) {
    if (!window.humhub) {
        return {url: url, guid: null};
    }

    return isHumhub()
        ? humhub.modules.file.filterFileUrl(url, mode)
        // Outside of HumHub there is no file module: return the same {url, guid} shape, resolving `file-guid:` urls
        : {
            url: url,
            guid: typeof url === 'string' && url.indexOf('file-guid:') === 0 ? url.substring('file-guid:'.length) : null,
        };
}

export function getLoaderWidget() {
    if (!window.humhub) {
        return $('<div class="loader">loading...</div>');
    }

    return humhub.require('ui.loader').set($('<span class="ProseMirror-placeholder">'), {
        span: true,
        size: '8px',
        css: {
            padding: '0px',
            width: '60px'
        }
    });
}

export function encode(str) {
    if (!window.humhub) {
        return $('<div/>').text(str).html();
    }

    return isHumhub() ? humhub.modules.util.string.encode(str) : str;
}

// TODO: Implement oembed provider interface
export function loadOembeds(urls) {
    if (!window.humhub) {
        return Promise.resolve([]);
    }

    return humhub.require('oembed').load(urls);
}

export function getOembed(url) {
    if (!window.humhub) {
        return null;
    }

    return humhub.require('oembed').get(url);
}

// TODO: Implement upload provider interface
export function getWidgetInstance(selector) {
    if (!window.humhub) {
        return null;
    }

    humhub.require('ui.widget.Widget').instance($(selector));
}
