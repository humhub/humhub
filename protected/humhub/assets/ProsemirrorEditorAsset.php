<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;

use humhub\components\assets\CoreAssetBundle;

class ProsemirrorEditorAsset extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/humhub-prosemirror-richtext/dist/';

    /**
     * @inheritdoc
     */
    public $js = ['humhub-editor.js'];
}
