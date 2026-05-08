<?php

namespace humhub\modules\content\widgets\richtext;

use humhub\helpers\Html;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\handler\UploadFileHandler;
use humhub\modules\file\widgets\UploadButton;

/**
 * Rich text editor implementation for the ProsemirrorRichText.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @see ProsemirrorRichText for a more detailed description of supported plugins and features.
 * @since 1.3
 */
class ProsemirrorRichTextEditor extends AbstractRichTextEditor
{
    public const MENU_CLASS_FOCUS = 'focusMenu';
    public const MENU_CLASS_PLAIN = 'plainMenu';

    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.richtext.prosemirror.RichTextEditor';

    /**
     * @var string defines the editor style, which will be added as class attribute
     */
    public $menuClass;

    public static $renderer = [
        'class' => ProsemirrorRichText::class,
    ];

    public function init()
    {
        if ($this->layout === static::LAYOUT_BLOCK) {
            $this->exclude[] = 'resizeNav';
            $this->menuClass = static::MENU_CLASS_PLAIN;
        } else {
            $this->menuClass = static::MENU_CLASS_FOCUS;
        }

        Html::addCssClass($this->options, ['ProsemirrorEditor', $this->menuClass]);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => $this->options['class'],
        ];
    }

    /**
     * Prepends the hidden UploadButton used by the file_handler ProseMirror plugin.
     * UploadFileHandler is excluded here because ProseMirror already provides its own
     * "Upload File" menu item via the upload plugin (menu.js), avoiding a duplicate entry.
     */
    public function prepend(): string
    {
        $handlers = FileHandlerCollection::getByType([
            FileHandlerCollection::TYPE_IMPORT,
            FileHandlerCollection::TYPE_CREATE,
        ]);

        // If only one handler, a button would be is rendered instead of a dropdown, so no need to prepend, as "Upload File" entry is already added to the menu via ProseMirror via upload/menu.js
        if (count($handlers) <= 1) {
            return '';
        }

        // Exclude only the generic UploadFileHandler ("Attach a file") because ProseMirror
        // already provides its own "Upload File" item via ProseMirror upload/menu.js.
        // Other handlers (Audio, Image, Video) are kept because they are distinct handlers.
        $handlers = array_values(
            array_filter(
                $handlers,
                fn($handler) => $handler::class !== UploadFileHandler::class,
            ),
        );

        return UploadButton::widget([
            'id' => $this->getId(true) . '-file-upload',
            'hideInStream' => true,
            'handlers' => $handlers,
            'cssDropdownButtonClass' => 'btn-group d-none' . ($this->hasModel() && $this->model->hasErrors($this->attribute) ? ' is-invalid' : ''),
        ]);
    }
}
