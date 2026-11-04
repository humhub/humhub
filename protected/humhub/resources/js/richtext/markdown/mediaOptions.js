/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

const DEFAULT_MEDIA_NODE_MAP = {
    video: 'video',
    audio: 'audio'
};

const MEDIA_OPTION_BLOCK_REGEXP = /^\{([^{}]+)\}$/;

const parseMediaOptionFlags = (rawValue) => {
    if (!rawValue || typeof rawValue !== 'string') {
        return [];
    }

    const flags = rawValue.trim().split(/\s+/).filter(Boolean);
    return [...new Set(flags)];
};

const parseMediaOptionBlock = (value) => {
    if (!value || typeof value !== 'string') {
        return null;
    }

    const match = value.trim().match(MEDIA_OPTION_BLOCK_REGEXP);
    if (!match) {
        return null;
    }

    return parseMediaOptionFlags(match[1]);
};

const getMediaFlagsFromToken = (token) => {
    if (!token || typeof token.attrGet !== 'function') {
        return [];
    }

    const optionsAttr = token.attrGet('media-options') || token.attrGet('data-media-options');
    if (optionsAttr) {
        return parseMediaOptionFlags(optionsAttr);
    }

    const title = token.attrGet('title');
    const titleFlags = parseMediaOptionBlock(title);
    return titleFlags || [];
};

const getMediaNodeNameFromFlags = (flags, mediaNodeMap = DEFAULT_MEDIA_NODE_MAP) => {
    for (let i = 0; i < flags.length; i++) {
        if (mediaNodeMap[flags[i]]) {
            return mediaNodeMap[flags[i]];
        }
    }

    return null;
};

const getBooleanAttrsFromTokenFlags = (token, booleanAttrs = []) => {
    const flags = getMediaFlagsFromToken(token);
    const result = {};

    booleanAttrs.forEach((attr) => {
        result[attr] = !!(token && token.attrGet && token.attrGet(attr)) || flags.includes(attr);
    });

    return result;
};

const buildMediaOptionBlock = (mediaKey, attrs = {}, booleanAttrs = []) => {
    if (!mediaKey) {
        return '';
    }

    const options = [mediaKey];

    booleanAttrs.forEach((attr) => {
        if (attrs[attr]) {
            options.push(attr);
        }
    });

    return '{' + options.join(' ') + '}';
};

export {
    DEFAULT_MEDIA_NODE_MAP,
    parseMediaOptionFlags,
    parseMediaOptionBlock,
    getMediaFlagsFromToken,
    getMediaNodeNameFromFlags,
    getBooleanAttrsFromTokenFlags,
    buildMediaOptionBlock
};
