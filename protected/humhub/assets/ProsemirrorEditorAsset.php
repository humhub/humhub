<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * The richtext editor (ProseMirror), built from `protected/humhub/resources/js/richtext/` into a committed
 * artifact - see docs/develop/concept-richtext.md.
 */
class ProsemirrorEditorAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    /**
     * @inheritdoc
     */
    public $js = ['js/humhub.richtext.editor.js'];
}
