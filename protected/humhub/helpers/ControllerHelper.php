<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Yii;

class ControllerHelper
{
    /**
     * Check the current path (module/controller/action) is active now
     *
     * @param string|null $moduleId
     * @param string|array $controllerIds
     * @param string|array $actionIds
     * @param array $queryParams
     * @return bool
     */
    public static function isActivePath(?string $moduleId = null, $controllerIds = [], $actionIds = [], array $queryParams = []): bool
    {
        if (!isset(Yii::$app->controller)) {
            return false;
        }

        if ($moduleId && (!Yii::$app->controller->module || Yii::$app->controller->module->id !== $moduleId)) {
            return false;
        }

        if (empty($controllerIds) && empty($actionIds)) {
            return true;
        }

        if ($controllerIds && !is_array($controllerIds)) {
            $controllerIds = [$controllerIds];
        }

        if (!empty($controllerIds) && !in_array(Yii::$app->controller->id, $controllerIds)) {
            return false;
        }

        if ($actionIds && !is_array($actionIds)) {
            $actionIds = [$actionIds];
        }

        if (!empty($actionIds) && !in_array(Yii::$app->controller->action->id, $actionIds)) {
            return false;
        }

        if (!empty($queryParams)) {
            return !empty(array_intersect_assoc(Yii::$app->request->queryParams, $queryParams));
        }

        return true;
    }
}
