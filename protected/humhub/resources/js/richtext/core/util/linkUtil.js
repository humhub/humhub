export const DEFAULT_LINK_REL = 'noopener noreferrer nofollow';

export function validateHref(href, cfg) {
    cfg = cfg || {};

    return /^https?:\/\//i.test(href) //http:/https:
        || /^mailto:/i.test(href) //mailto:
        || /^ftps?:\/\//i.test(href) //ftp:/ftps:
        || (cfg.anchor && validateAnchor(href)) //anchor
        || (cfg.relative && validateRelative(href)); //relative
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
