<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;

/**
 * The actions of a header, as icon buttons: the tools first and the primary entry
 * ({@see MenuLink::getIsPrimary()}) last, whatever the sort order. A menu with more than one
 * primary entry shows the first as primary and the others as tools. A header's ⋮ menu is a
 * {@see DropdownMenu} of its own.
 *
 * The base of {@see PageHeaderActions} and {@see CardHeaderActions}; they differ in the look of
 * the tools.
 *
 * @since 1.20
 */
abstract class HeaderActions extends Menu
{
    /**
     * @inheritdoc
     */
    public $template = '@humhub/widgets/menu/views/header-actions';

    /**
     * @var string the button variant of the tools
     */
    public string $toolVariant = 'secondary';

    /**
     * The visible link entries, the tools in sort order and the primary one apart.
     *
     * @return array{tools: MenuLink[], primary: MenuLink|null}
     */
    public function getToolsAndPrimary(): array
    {
        $tools = [];
        $primary = null;

        foreach ($this->getSortedEntries() as $entry) {
            if (!$entry instanceof MenuLink || !$entry->isVisible()) {
                continue;
            }

            if ($entry->getIsPrimary() && $primary === null) {
                $primary = $entry;
            } else {
                $tools[] = $entry;
            }
        }

        return ['tools' => $tools, 'primary' => $primary];
    }

    /**
     * @inheritdoc
     */
    protected function getViewParams()
    {
        return array_merge(parent::getViewParams(), $this->getToolsAndPrimary());
    }
}
