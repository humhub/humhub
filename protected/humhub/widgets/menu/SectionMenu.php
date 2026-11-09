<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;

/**
 * The menu of an area with several sections, next to the section's page: a card with the area's title and one
 * entry per section (Administration, My Settings). The active entry is filled, an entry with a `count` above 0
 * shows the waiting dot. See docs/ui/master-detail.md.
 *
 * @since 1.20
 */
abstract class SectionMenu extends Menu
{
    /**
     * @var string|null the area's title: the card's heading, and the menu's name for assistive technology
     */
    public ?string $title = null;

    /**
     * @inheritdoc
     */
    public $template = '@humhub/widgets/menu/views/section-menu.php';

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'card section-menu',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        if ($this->hasTitle()) {
            $options['aria-labelledby'] = $this->getTitleId();
        }

        return $options;
    }

    public function hasTitle(): bool
    {
        return $this->title !== null && $this->title !== '';
    }

    /**
     * @return string the id of the title element
     */
    public function getTitleId(): string
    {
        return $this->getId() . '-title';
    }
}
