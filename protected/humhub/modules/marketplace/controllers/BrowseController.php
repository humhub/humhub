<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\marketplace\Module;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\HttpException;

/**
 * Class BrowseController
 *
 * @property Module $module
 * @package humhub\modules\marketplace\controllers
 */
class BrowseController extends Controller
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
     * Complete list of all modules
     */
    public function actionList()
    {
        $keyword = Yii::$app->request->post('keyword', "");
        $categoryId = (int)Yii::$app->request->post('categoryId', 0);
        $hideInstalled = (boolean)Yii::$app->request->post('hideInstalled');

        $onlineModules = $this->module->onlineModuleManager;
        $modules = $onlineModules->getModules();
        $categories = $onlineModules->getCategories();

        foreach ($modules as $i => $module) {
            if (!empty($categoryId) && !in_array($categoryId, $module['categories'])) {
                unset($modules[$i]);
            }

            if (!empty($keyword) && stripos($module['name'], $keyword) === false && stripos($module['description'], $keyword) === false) {
                unset($modules[$i]);
            }

            if ($hideInstalled && Yii::$app->moduleManager->hasModule($module['id'])) {
                unset($modules[$i]);
            }

            if ($this->module->hideLegacyModules && !empty($module['isDeprecated'])) {
                unset($modules[$i]);
            }
        }

        return $this->render('list', [
            'modules' => $modules,
            'keyword' => $keyword,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'hideInstalled' => $hideInstalled,
            'licence' => $this->module->getLicence()
        ]);
    }


    /**
     * Returns the thirdparty disclaimer
     *
     * @throws HttpException
     */
    public function actionThirdpartyDisclaimer()
    {
        return $this->renderAjax('thirdpartyDisclaimer', []);
    }


    /**
     * Installs a given moduleId from marketplace
     */
    public function actionInstall()
    {
        $this->forcePostRequest();

        $moduleId = Yii::$app->request->get('moduleId');

        if (!Yii::$app->moduleManager->hasModule($moduleId)) {
            $this->module->onlineModuleManager->install($moduleId);
        }

        return $this->redirect(['/admin/module/list']);
    }

}
