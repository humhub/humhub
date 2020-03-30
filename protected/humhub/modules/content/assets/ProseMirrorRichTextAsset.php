<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\assets;

use humhub\assets\ProsemirrorEditorAsset;
use humhub\components\assets\AssetBundle;
/**
 * Asset for core content resources.
 *
 * @since 1.3
 * @author buddha
 */
class ProseMirrorRichTextAsset extends AssetBundle
{
     /**
     * @inheritdoc
     */
    public $sourcePath = '@content/resources';

     /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.ui.richtext.prosemirror.js'
    ];

     /**
     * @inheritdoc
     */
    public $depends = [
        ProsemirrorEditorAsset::class
    ];

}
