<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\models\AdvancedSettingsSpace;
use humhub\modules\space\widgets\Chooser;
use humhub\modules\space\modules\manage\components\Controller;
use humhub\modules\space\modules\manage\models\DeleteForm;

/**
 * Default space admin action
 *
 * @author Luke
 */
class DefaultController extends Controller
{

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        $result = parent::getAccessRules();
        $result[] = [
            'userGroup' => [Space::USERGROUP_OWNER], 'actions' => ['archive', 'unarchive', 'delete']
        ];
        return $result;
    }

    /**
     * General space settings
     */
    public function actionIndex()
    {
        $space = $this->contentContainer;
        $space->scenario = 'edit';

        if ($space->load(Yii::$app->request->post()) && $space->validate() && $space->save()) {
            $this->view->saved();
            return $this->redirect($space->createUrl('index'));
        }
        return $this->render('index', ['model' => $space]);
    }

    public function actionAdvanced()
    {
        $space = AdvancedSettingsSpace::findOne(['id' => $this->contentContainer->id]);
        $space->scenario = 'edit';
        $space->indexUrl = Yii::$app->getModule('space')->settings->space()->get('indexUrl');
        $space->indexGuestUrl = Yii::$app->getModule('space')->settings->space()->get('indexGuestUrl');
        
        if ($space->load(Yii::$app->request->post()) && $space->validate() && $space->save()) {
            $this->view->saved();
            return $this->redirect($space->createUrl('advanced'));
        }

        $indexModuleSelection = \humhub\modules\space\widgets\Menu::getAvailablePages();

        //To avoid infinit redirects of actionIndex we remove the stream value and set an empty selection instead
        array_shift($indexModuleSelection);
        $indexModuleSelection = ["" => Yii::t('SpaceModule.controllers_AdminController', 'Stream (Default)')] + $indexModuleSelection;

        return $this->render('advanced', ['model' => $space, 'indexModuleSelection' => $indexModuleSelection]);
    }

    /**
     * Archives the space
     */
    public function actionArchive()
    {
        $space = $this->getSpace();
        $space->archive();
        
        if(Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            return [
                'success' => true,
                'space' => Chooser::getSpaceResult($space, true, ['isMember' => true])
            ];
        }
        
        return $this->redirect($space->createUrl('/space/manage'));
    }

    /**
     * Unarchives the space
     */
    public function actionUnarchive()
    {
        $space = $this->getSpace();
        $space->unarchive();
        
        if(Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            return [
                'success' => true,
                'space' => Chooser::getSpaceResult($space, true, ['isMember' => true])
            ];
        }
        
        return $this->redirect($space->createUrl('/space/manage'));
    }

    /**
     * Deletes this Space
     */
    public function actionDelete()
    {
        $model = new DeleteForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->getSpace()->delete();
            return $this->goHome();
        }

        return $this->render('delete', ['model' => $model, 'space' => $this->getSpace()]);
    }

}

?>
