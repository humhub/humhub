<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;


use humhub\modules\content\assets\ProseMirrorRichTextAsset;
use humhub\modules\file\widgets\UploadInput;

/**
 * Rich text editor implementation for the ProsemirrorRichText.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @see ProsemirrorRichText for a more detailed description of supported plugins and features.
 * @since 1.3
 */
class ProsemirrorRichTextEditor extends AbstractRichTextEditor
{

    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.richtext.prosemirror.RichTextEditor';

    public static  $renderer = [
        'class' => ProsemirrorRichText::class
    ];

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'ProsemirrorEditor'
        ];
    }

    /**
     * Prepends an upload input form element to the rich text editor used by the upload editor plugin.
     */
    public function prepend()
    {
        return UploadInput::widget([
            'id' => $this->getId(true).'-file-upload',
            'hideInStream' => true
        ]);
    }
}