<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\models\filters;

use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use ReflectionException;

class ModuleStreamFilter extends StreamQueryFilter
{
    public function apply(): void
    {
        $this->excludeContainersWithNotAvailableModule(Space::class);
        if (!$this->streamQuery->container instanceof Space) {
            $this->excludeContainersWithNotAvailableModule(User::class);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function excludeContainersWithNotAvailableModule(string $containerClass): void
    {
        $excludedContentClasses = ContentContainerModuleState::getExcludedContentClasses($containerClass);
        if (empty($excludedContentClasses)) {
            return;
        }

        $this->query->andWhere(['OR',
            ['!=', 'contentcontainer.class', $containerClass],
            ['AND', ['contentcontainer.class' => $containerClass], ['NOT IN', 'content.object_model', $excludedContentClasses]]
        ]);
    }
}
