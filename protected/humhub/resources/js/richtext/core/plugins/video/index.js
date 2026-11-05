/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {schema} from "./schema";
import {videoPlugin} from "./plugin";
import {filterFileUrl} from "../../humhub-bridge";
import {validateHref} from "../../util/linkUtil";
import {parseMediaOptionFlags} from "../../../markdown/mediaOptions";
import {getClassForFloat} from "../image/imageFloat";

const FILE_GUID_PREFIX = 'file-guid:';

const normalizeFileUrl = (raw, source) => {
    if (raw && typeof raw === 'object') {
        return raw;
    }

    if (typeof source === 'string' && source.indexOf(FILE_GUID_PREFIX) === 0) {
        return {
            url: source,
            guid: source.substring(FILE_GUID_PREFIX.length)
        };
    }

    return {
        url: source,
        guid: null
    };
};

const hasFlag = (token, attr) => {
    if (token.attrGet(attr)) {
        return true;
    }

    const options = token.attrGet('media-options');
    if (!options) {
        return false;
    }

    return parseMediaOptionFlags(options).includes(attr);
};

const setBooleanAttr = (token, attr) => {
    if (hasFlag(token, attr)) {
        token.attrSet(attr, attr);
    }
};

const removeAttr = (token, attr) => {
    const attrIndex = token.attrIndex(attr);
    if (attrIndex >= 0) {
        token.attrs.splice(attrIndex, 1);
    }
};

const video = {
    id: 'video',
    schema: schema,
    plugins: (context) => {
        return [
            videoPlugin(context)
        ];
    },
    registerMarkdownIt: (markdownIt) => {
        markdownIt.renderer.rules.video = (tokens, idx, options, env, self) => {
            const token = tokens[idx];
            const srcIndex = token.attrIndex('src');
            const title = token.attrGet('title') || token.attrGet('alt');

            if (srcIndex >= 0) {
                const source = token.attrs[srcIndex][1];
                let srcFilter = normalizeFileUrl(filterFileUrl(source), source);
                token.attrs[srcIndex][1] = validateHref(srcFilter.url) ? srcFilter.url : '#';

                if (srcFilter.guid) {
                    token.attrSet('data-file-guid', srcFilter.guid);
                }
            }

            if (title) {
                token.attrSet('title', title);
            }

            setBooleanAttr(token, 'controls');
            setBooleanAttr(token, 'autoplay');
            setBooleanAttr(token, 'muted');
            setBooleanAttr(token, 'loop');
            if (token.attrGet('float')) {
                token.attrSet('class', getClassForFloat(token.attrGet('float')));
            }
            ['media-options', 'video', 'alt', 'float'].forEach((attr) => removeAttr(token, attr));

            token.attrSet('playsinline', 'playsinline');
            return '<video' + self.renderAttrs(token) + '></video>';
        };
    }
};

export default video;
