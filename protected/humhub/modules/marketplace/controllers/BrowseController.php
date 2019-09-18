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

        $onlineModules = $this->module->onlineModuleManager;
        $modules = $onlineModules->getModules();

        if ($keyword != "") {
            $results = [];
            foreach ($modules as $module) {
                if (stripos($module['name'], $keyword) !== false || stripos($module['description'], $keyword) !== false) {
                    $results[] = $module;
                }
            }
            $modules = $results;
        }

        return $this->render('list', ['modules' => $modules, 'keyword' => $keyword]);
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
