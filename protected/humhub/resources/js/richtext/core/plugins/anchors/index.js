/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import anchor_plugin from "markdown-it-anchor";
import {showSuccessNotify} from "../../humhub-bridge";

const copyHrefToClipboard = (target, context) => {
    const href = target.href || target.parentElement.href;
    if (href) {
        const successMsg = context.translate('Link has been copied to clipboard');
        navigator.clipboard.writeText(href).then(r => showSuccessNotify(successMsg));
    }
}

const anchors = {
    id: 'anchor',
    renderOnly: true,
    init: (context, isEdit) => {
        if (!isEdit) {
            context.editor.$.on('mouseenter', ':header', function () {
                $(this).find('.header-anchor').show().on('click', (e) => copyHrefToClipboard(e.target, context));
            }).on('mouseleave', ':header', function () {
                $(this).find('.header-anchor').hide().off('click');
            });
        }
    },
    registerMarkdownIt: (markdownIt) => {
        const anchorOptions = {
            permalink: anchor_plugin.permalink.linkInsideHeader({
                symbol: '<i class="fa fa-chain"></i>',
                placement: 'after',
                ariaHidden: true,
                renderAttrs: (slug, state) => {
                    return {'style': 'display:none'};
                }
            })
        };

        markdownIt.use(anchor_plugin, anchorOptions);
    }
};

export default anchors;
