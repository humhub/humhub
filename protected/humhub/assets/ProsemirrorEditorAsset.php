<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;


use yii\web\AssetBundle;

class ProsemirrorEditorAsset extends AssetBundle
{
    public $publishOptions = [
        'forceCopy' => false
    ];

    /**
     * @inheritdoc
     */
    //public $sourcePath = '/codebase/humhub/humhub-prosemirror/dist/';
    public $sourcePath = '@npm/humhub-prosemirror-richtext/dist/';

    /**
     * @inheritdoc
     */
    public $js = ['humhub-editor.js'];
}
