/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

// Process [link](oembed:<href>)

import {createLinkExtension} from "../../../markdown/linkExtensionTokenizer"

let oembed_plugin = createLinkExtension('oembed', {node : 'div'});

export {oembed_plugin}
