<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\WidgetMenuEntry;
use humhub\modules\ui\menu\widgets\Menu;

/**
 * This widget renders the controls menu for a Comment.
 *
 * @since 1.10
 */
class CommentControls extends Menu
{

    /**
     * @var Comment
     */
    public $comment;

    /**
     * @inheritdoc
     */
    public $template = '@comment/widgets/views/commentControls';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initControls();
    }

    public function initControls()
    {
        $entries = $this->getControlsMenuEntries();

        foreach ($entries as $menuEntry) {
            if (!empty($menuEntry)) {
                $this->addEntry($this->getMenuEntry($menuEntry));
            }
        }
    }

    /**
     * Returns an array of comment menu items:
     *
     * @return array
     */
    public function getControlsMenuEntries(): array
    {
        return [
            [PermaLink::class, ['comment' => $this->comment], ['sortOrder' => 100]],
            [EditLink::class, ['comment' => $this->comment], ['sortOrder' => 200]],
            [DeleteLink::class, ['comment' => $this->comment], ['sortOrder' => 300]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav nav-pills preferences'
        ];
    }

    /**
     * Returns the widget definition for the given $menuItem.
     * The $menuItem can either be given as MenuEntry object or as widget type definition:
     *
     * [MyWidget::class, [...], [...]]
     *
     * @param array|MenuEntry $menuItem
     * @return MenuEntry
     */
    protected function getMenuEntry($menuItem)
    {
        if ($menuItem instanceof MenuEntry) {
            return $menuItem;
        }

        $options = isset($menuItem[2]) ? $menuItem[2] : [];

        $cfg = array_merge($options, [
            'widgetClass' => $menuItem[0],
            'widgetOptions' => isset($menuItem[1]) ? $menuItem[1] : null,
            'sortOrder' => isset($options['sortOrder']) ? $options['sortOrder'] : PHP_INT_MAX,
        ]);

        return new WidgetMenuEntry($cfg);
    }

}
