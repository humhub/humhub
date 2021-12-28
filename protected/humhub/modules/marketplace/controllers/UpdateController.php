<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\components\Module;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageModules;
use Yii;
use yii\web\HttpException;

/**
 * Class UpdateController
 *
 * @property \humhub\modules\marketplace\Module $module
 * @package humhub\modules\marketplace\controllers
 */
class UpdateController extends Controller
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => ManageModules::class]
        ];
    }

    /**
     * Updates a module with the most recent version online
     *
     * @return UpdateController|\yii\console\Response|\yii\web\Response
     * @throws HttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\ErrorException
     */
    public function actionInstall()
    {
        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');

        /** @var Module $module */
        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            throw new HttpException(500, Yii::t('AdminModule.modules', 'Could not find requested module!'));
        }

        $moduleInfo = $this->module->onlineModuleManager->getModuleInfo($moduleId);

        if (empty($moduleInfo['latestCompatibleVersion']['downloadUrl'])) {
            if (!empty($moduleInfo['isPaid'])) {
                $error = Yii::t('AdminModule.modules', 'License not found or expired. Please contact the module publisher.');
            } else {
                $error = 'Could not determine module download url from HumHub API response.';
                Yii::error($error, 'marketplace');
            }
            throw new HttpException(500, $error);
        }

        $this->module->onlineModuleManager->update($moduleId);

        try {
            $module->publishAssets(true);
        } catch (\Exception $e) {
            Yii::error($e);
        }

        return $this->asJson([
            'success' => true,
            'status' => Yii::t('AdminModule.modules', 'Update successful'),
            'message' => Yii::t('AdminModule.modules', 'Module "{moduleName}" has been updated to version {newVersion} successfully.', [
                'moduleName' => $moduleInfo['latestCompatibleVersion']['name'],
                'newVersion' => $moduleInfo['latestCompatibleVersion']['version'],
            ]),
        ]);
    }

}
