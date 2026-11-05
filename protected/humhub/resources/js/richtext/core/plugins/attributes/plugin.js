/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import { Plugin } from 'prosemirror-state';

const attributesPlugin = (context) => {
    return new Plugin({
        props: {
            attributes: context.options.attributes
        }
    });
};

export {attributesPlugin}