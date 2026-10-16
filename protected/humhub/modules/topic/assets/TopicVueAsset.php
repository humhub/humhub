<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\assets;

use humhub\components\assets\VueAssetBundle;

/**
 * Compiled Vue components of the topic module (`vue/`, built via
 * `grunt build-vue --module=topic`): the `topic` filter type of `FilterBar`
 * (`TopicFilterControl`) — a page whose filter bar has a
 * {@see \humhub\modules\topic\components\listing\TopicFilter} lists this bundle in its
 * asset bundle's `$depends`.
 *
 * @since 1.20
 */
class TopicVueAsset extends VueAssetBundle
{
}
