<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\models\filters;

use humhub\models\Setting;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use ReflectionClass;
use Yii;

class ModuleStreamFilter extends StreamQueryFilter
{
    public function apply(): void
    {
        $this->excludeContainersWithNotAvailableModule(Space::class);
        if (!$this->streamQuery->container instanceof Space) {
            $this->excludeContainersWithNotAvailableModule(User::class);
        }
    }

    private function excludeContainersWithNotAvailableModule(string $containerClass): void
    {
        $reflectClass = new ReflectionClass($containerClass);

        // Find modules with "Not Available" option per User/Space
        $moduleIds = Setting::find()
            ->select('module_id')
            ->where(['name' => 'moduleManager.defaultState.' . $reflectClass->getShortName()])
            ->andWhere(['value' => ContentContainerModuleState::STATE_NOT_AVAILABLE])
            ->column();

        if (empty($moduleIds)) {
            return;
        }

        $excludedContentClasses = [];
        foreach ($moduleIds as $moduleId) {
            $module = Yii::$app->getModule($moduleId);
            if ($module instanceof ContentContainerModule) {
                $excludedContentClasses = array_merge($excludedContentClasses, $module->getContentClasses());
            }
        }

        if (empty($excludedContentClasses)) {
            return;
        }

        $this->query->andWhere(['OR',
            ['!=', 'contentcontainer.class', $containerClass],
            ['AND', ['contentcontainer.class' => $containerClass], ['NOT IN', 'content.object_model', $excludedContentClasses]]
        ]);
    }
}
