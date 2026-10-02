<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\unit;

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * A content container module for tests; its supported container types can be set.
 */
class TestContainerModule extends ContentContainerModule
{
    public array $containerTypes = [Space::class, User::class];

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return $this->containerTypes;
    }
}
