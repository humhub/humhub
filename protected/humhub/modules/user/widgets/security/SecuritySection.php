<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\components\Widget;

/**
 * A section in a tab of the security settings, rendered as a card with title, description and content
 *
 * Modules add sections to [[SecurityLoginSections]], [[SecurityPrivacySections]] or [[SecurityDataSections]] on their `EVENT_INIT`.
 *
 * @since 1.20
 */
abstract class SecuritySection extends Widget
{
    abstract public function getTitle(): string;

    public function getDescription(): string
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

    public function run()
    {
        if (!$this->isVisible()) {
            return '';
        }

        return $this->render('@user/widgets/security/views/section', [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'content' => $this->renderContent(),
        ]);
    }
}
