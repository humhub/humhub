export const DEFAULT_LINK_REL = 'noopener noreferrer nofollow';

export function validateHref(href, cfg) {
    cfg = cfg || {};

    return /^https?:\/\//i.test(href) //http:/https:
        || /^mailto:/i.test(href) //mailto:
        || /^ftps?:\/\//i.test(href) //ftp:/ftps:
        || (cfg.anchor && validateAnchor(href)) //anchor
        || (cfg.relative && validateRelative(href)); //relative
}

// The scheme of a link of an editor extension, e.g. `linkpreview` of `linkpreview:https://…`
// (see RichTextLinkExtension). Schemes a browser would execute or open as a document are never an extension.
const EXTENSION_HREF = /^([a-z][a-z0-9_-]+):/i;
const UNSAFE_SCHEMES = ['javascript', 'data', 'vbscript', 'file', 'blob'];

/**
 * Whether the href is an extension link `<extension>:<value>` that is not (yet) known to the editor, e.g. the link of a
 * module whose plugin is not enabled. Such a link is kept as it is, and not replaced by `#`, so the editor does not
 * drop its value when the text is edited.
 */
export function isExtensionHref(href) {
    const match = EXTENSION_HREF.exec(href || '');

    return !!match && !UNSAFE_SCHEMES.includes(match[1].toLowerCase());
}

/**
 * The url of an extension link `<extension>:<http(s) url>`, e.g. `https://example.com` of
 * `linkpreview:https://example.com`, which is what a reader without the extension gets as the link target.
 * Null for any other href.
 */
export function extensionTargetUrl(href) {
    const match = /^([a-z][a-z0-9_-]+):(https?:\/\/.+)$/i.exec(href || '');

    return match && !UNSAFE_SCHEMES.includes(match[1].toLowerCase()) ? match[2] : null;
}

export function validateRelative(href) {
    // eslint-disable-next-line
    return /^\/[^\/].*$/i.test(href);
}

export function validateAnchor(href) {
    return /^#((?:[!$&()*+,;=._~:@?-]|%[0-9a-fA-F]{2}|[a-zA-Z0-9])+)$/i.test(href);
}

export function buildLink(href, attrs, text, validate) {
    attrs = attrs || {};

    if(validate !== false) {
        href = validateHref(href, validate) ? href : '#';
    }

    text = text || href;

    let defaultAttrs = {href: href};

    if(href !== '#') {
        defaultAttrs.target = '_blank';
        defaultAttrs.rel = DEFAULT_LINK_REL;
    }

    return $('<div>').append($('<a>').attr($.extend(defaultAttrs, attrs)).text(text)).html();
}
