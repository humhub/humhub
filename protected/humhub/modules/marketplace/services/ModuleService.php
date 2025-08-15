<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\services;

use Exception;
use humhub\components\Module as CoreModule;
use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @since 1.15
 */
class ModuleService
{
    public ?CoreModule $module = null;

    /**
     * @var string|null Originally provided module ID for case when module is not installed yet
     */
    public ?string $moduleId = null;

    /**
     * @param string|CoreModule|null $module Module or Module ID
     */
    public function __construct($module = null)
    {
        $this->init($module);
    }

    /**
     * @param string|CoreModule|null $module Module or Module ID
     */
    public function init($module = null)
    {
        if (is_string($module)) {
            $this->moduleId = $module;
            $this->module = $this->getById($module);
        } elseif ($module instanceof CoreModule) {
            $this->moduleId = $module->id;
            $this->module = $module;
        }
    }

    public function getById(string $id): ?CoreModule
    {
        return Yii::$app->moduleManager->getModule($id, false);
    }

    public function getOnlineModuleManager(): OnlineModuleManager
    {
        /* @var MarketplaceModule $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        return $marketplaceModule->onlineModuleManager;
    }

    public function isInstalled(): bool
    {
        return $this->module instanceof CoreModule
            && Yii::$app->moduleManager->hasModule($this->module->id);
    }

    public function install(): bool
    {
        if (!$this->isInstalled()) {
            $this->getOnlineModuleManager()->install($this->moduleId);
        }

        return true;
    }

    /**
     * @return bool
     * @deprecated since v1.16; use static::enable()
     * @see static::enable()
     */
    public function activate(): bool
    {
        return $this->enable();
    }

    public function enable(): bool
    {
        return $this->module instanceof CoreModule && $this->module->enable() !== false;
    }

    public function update(): array
    {
        if ($this->module === null) {
            throw new NotFoundHttpException(Yii::t('MarketplaceModule.base', 'Could not find requested module!'));
        }

        $moduleInfo = $this->getOnlineModuleManager()->getModuleInfo($this->moduleId);

        if (empty($moduleInfo['latestCompatibleVersion']['downloadUrl'])) {
            if (!empty($moduleInfo['isPaid'])) {
                $error = Yii::t('MarketplaceModule.base', 'License not found or expired. Please contact the module publisher.');
            } else {
                $error = 'Could not determine module download url from HumHub API response.';
                Yii::error($error, 'marketplace');
            }
            throw new ServerErrorHttpException($error);
        }

        $this->getOnlineModuleManager()->update($this->moduleId);

        try {
            $this->module->publishAssets(true);
        } catch (Exception $e) {
            Yii::error($e);
        }

        return [
            'success' => true,
            'status' => Yii::t('MarketplaceModule.base', 'Update successful'),
            'message' => Yii::t('MarketplaceModule.base', 'Module "{moduleName}" has been updated to version {newVersion} successfully.', [
                'moduleName' => $moduleInfo['latestCompatibleVersion']['name'],
                'newVersion' => $moduleInfo['latestCompatibleVersion']['version'],
            ]),
        ];
    }
}
