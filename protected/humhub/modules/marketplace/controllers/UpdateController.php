<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\services\ModuleService;
use Yii;
use yii\web\HttpException;

/**
 * Class UpdateController
 *
 * @package humhub\modules\marketplace\controllers
 */
class UpdateController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
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

        $moduleService = new ModuleService(Yii::$app->request->get('moduleId'));

        return $this->asJson($moduleService->update());
    }

}
