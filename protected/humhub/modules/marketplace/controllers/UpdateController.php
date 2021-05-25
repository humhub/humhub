<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\components\Module;
use humhub\modules\admin\components\Controller;
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
     * @var string
     */
    public $defaultAction = 'list';

    /**
     * @var string
     */
    public $subLayout = '@admin/views/layouts/module';

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => \humhub\modules\admin\permissions\ManageModules::class]
        ];
    }

    /**
     * Lists all available module updates
     */
    public function actionList()
    {
        // Include Community Modules Form Submit
        if (!empty(Yii::$app->request->get('betaSwitch'))) {
            $this->module->settings->set('includeBetaUpdates', (empty(Yii::$app->request->post('includeBetaUpdates'))) ? 0 : 1);
        }
        $includeBetaUpdates = (boolean)$this->module->settings->get('includeBetaUpdates');

        $modules = $this->module->onlineModuleManager->getModuleUpdates(false);
        return $this->render('list', ['modules' => $modules, 'includeBetaUpdates' => $includeBetaUpdates]);
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
                $this->view->setStatusMessage('error', Yii::t('AdminModule.modules', 'License not found or expired. Please contact the module publisher.'));
            } else {
                Yii::error('Could not determine module download url from HumHub API response.', 'marketplace');
            }
            return $this->redirect(['/marketplace/update/list']);
        }

        $this->module->onlineModuleManager->update($moduleId);


        try {
            $module->publishAssets(true);
        } catch (\Exception $e) {
            Yii::error($e);
        }

        return $this->redirect(['/marketplace/update/list']);
    }

}
