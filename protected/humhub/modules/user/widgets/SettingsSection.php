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
 * Modules add sections to a [[SettingsSectionStack]] on its `EVENT_INIT`, e.g. to
 * [[account\AccountGeneralSections]] or [[security\SecurityLoginSections]].
 *
 * @since 1.20
 */
abstract class SettingsSection extends Widget
{
    /**
     * @var string|null the id of the accordion the section is an item of, set by the stack; null renders a plain section
     */
    public ?string $accordionId = null;

    /**
     * @var bool whether the accordion item starts open
     */
    public bool $expanded = false;

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

    /**
     * @return bool whether the section's form was submitted with errors - an accordion opens it instead of its first item
     */
    public function hasErrors(): bool
    {
        return false;
    }

    abstract protected function renderContent(): string;

    public function run()
    {
        if (!$this->isVisible()) {
            return '';
        }

        $params = [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'actions' => $this->getActions(),
            'content' => $this->renderContent(),
        ];

        if ($this->accordionId === null) {
            return $this->render('@user/widgets/views/settingsSection', $params);
        }

        return $this->render('@user/widgets/views/settingsAccordionItem', $params + [
            'id' => $this->getId(),
            'accordionId' => $this->accordionId,
            'expanded' => $this->expanded,
        ]);
    }
}
