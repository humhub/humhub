<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use Yii;
use humhub\modules\space\modules\manage\components\Controller;

/**
 * Space module management
 *
 * @author Luke
 */
class ModuleController extends Controller
{

    /**
     * Modules Administration Action
     */
    public function actionIndex()
    {
        $space = $this->getSpace();
        return $this->render('index', ['availableModules' => $space->getAvailableModules(), 'space' => $space]);
    }

    /**
     * Enables a space module
     *
     * @return string the output
     */
    public function actionEnable()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();

        $moduleId = Yii::$app->request->get('moduleId', "");

        if (!$space->isModuleEnabled($moduleId)) {
            $space->enableModule($moduleId);
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect($space->createUrl('/space/manage/module'));
        } else {
            Yii::$app->response->format = 'json';
            return [];
        }
    }


    /**
     * Disables a space module
     *
     * @return string the output
     */
    public function actionDisable()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();

        $moduleId = Yii::$app->request->get('moduleId', "");

        if ($space->isModuleEnabled($moduleId) && $space->canDisableModule($moduleId)) {
            $space->disableModule($moduleId);
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect($space->createUrl('/space/manage/module'));
        } else {
            Yii::$app->response->format = 'json';
            return [];
        }

    }

}

?>
