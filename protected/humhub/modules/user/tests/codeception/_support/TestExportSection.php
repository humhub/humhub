<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\_support;

use humhub\modules\user\widgets\security\SecuritySection;

/**
 * A section a module would add to the "My Data" tab of the security settings
 *
 * @since 1.20
 */
class TestExportSection extends SecuritySection
{
    public function getTitle(): string
    {
        return 'Test export section';
    }

    protected function renderContent(): string
    {
        return '';
    }
}
