<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;

/**
 * A section of an account settings tab, rendered with title, description and content
 *
 * Modules add sections to a [[SettingsSectionStack]] on its `EVENT_INIT`, e.g. to [[security\SecurityLoginSections]].
 *
 * @since 1.20
 */
abstract class SettingsSection extends Widget
{
    abstract public function getTitle(): string;

    public function getDescription(): string
    {
        return '';
    }

    /**
     * @return string the section's actions next to its title (HTML), e.g. a small button adding an
     * entry or acting on all of them
     */
    public function getActions(): string
    {
        return '';
    }

    /**
     * @return bool whether the section is shown for the current user - a tab without visible sections is hidden
     */
    public function isVisible(): bool
    {
        return true;
    }

    abstract protected function renderContent(): string;

    /**
     * Renders the section as a group of the accordion of its stack ({@see SettingsSectionStack::$accordion})
     *
     * @param string $accordionId the id of the accordion
     * @param string $id the id of the group's body
     * @param bool $open whether the group is open
     */
    public function renderAccordionItem(string $accordionId, string $id, bool $open): string
    {
        return $this->render('@user/widgets/views/settingsSectionAccordionItem', [
            'accordionId' => $accordionId,
            'id' => $id,
            'open' => $open,
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'actions' => $this->getActions(),
            'content' => $this->renderContent(),
        ]);
    }

    public function run()
    {
        if (!$this->isVisible()) {
            return '';
        }

        return $this->render('@user/widgets/views/settingsSection', [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'actions' => $this->getActions(),
            'content' => $this->renderContent(),
        ]);
    }
}
