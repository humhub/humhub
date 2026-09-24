<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\helpers\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\widgets\Icon;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The "Edit" entry of a content's context menu ({@see WallEntryControls}), in the edit mode
 * of the stream entry: inline, in a modal, or on a page of its own.
 *
 * Inline editing renders a second, hidden "Cancel Edit" anchor next to the entry, which the
 * stream entry script swaps in while the form is open (`humhub.stream.StreamEntry.js`). It is
 * part of the rendered markup only; a client rendering the menu itself gets the entry alone
 * ({@see self::describe()}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 0.10
 */
class EditLink extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $model = null;

    /**
     * @var string edit route.
     */
    public $url;

    /**
     * @var string the edit mode of the wall entry, one of the `WallStreamEntryWidget::EDIT_MODE_*` values
     */
    public $mode;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $modes = [
            WallStreamEntryWidget::EDIT_MODE_INLINE,
            WallStreamEntryWidget::EDIT_MODE_MODAL,
            WallStreamEntryWidget::EDIT_MODE_NEW_WINDOW,
        ];

        if (!$this->url || !in_array($this->mode, $modes, true) || !$this->model->content->canEdit()) {
            $this->setIsVisible(false);
            return;
        }

        // `$url` is the edit route and shares its name with the link url of `MenuLink`, which
        // setUrl() writes - so the route is taken before the link url is set.
        $editUrl = $this->url;

        $this->setLabel(Yii::t('ContentModule.base', 'Edit'));
        $this->setIcon('edit');

        if ($this->mode === WallStreamEntryWidget::EDIT_MODE_NEW_WINDOW) {
            $this->setUrl($editUrl);
            $this->setHtmlOptions(['class' => 'stream-entry-edit-link']);
            return;
        }

        $this->setUrl('#');
        $this->url = $editUrl;
        $this->setHtmlOptions([
            'class' => 'stream-entry-edit-link',
            'data-action-click' => $this->mode === WallStreamEntryWidget::EDIT_MODE_INLINE ? 'edit' : 'editModal',
            'data-action-url' => $editUrl,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderEntry($extraHtmlOptions = [])
    {
        $html = parent::renderEntry($extraHtmlOptions);

        if ($this->mode !== WallStreamEntryWidget::EDIT_MODE_INLINE) {
            return $html;
        }

        $cancelOptions = [
            'class' => 'stream-entry-cancel-edit-link',
            'data-action-click' => 'cancelEdit',
            'style' => 'display:none;',
        ];
        Html::addCssClass($cancelOptions, $extraHtmlOptions['class'] ?? []);

        return $html . Html::a(
            Icon::get('edit') . ' ' . Yii::t('ContentModule.base', 'Cancel Edit'),
            '#',
            $cancelOptions,
        );
    }
}
