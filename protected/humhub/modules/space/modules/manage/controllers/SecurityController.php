<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use Yii;
use humhub\modules\space\modules\manage\components\Controller;
use humhub\modules\space\models\Space;
use yii\web\HttpException;

/**
 * SecurityController
 * 
 * @since 1.1
 * @author Luke
 */
class SecurityController extends Controller
{

    public function actionIndex()
    {
        $space = $this->contentContainer;
        $space->scenario = 'edit';

        if ($space->load(Yii::$app->request->post()) && $space->validate() && $space->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
            return $this->redirect($space->createUrl('index'));
        }
        return $this->render('index', ['model' => $space]);
    }

    /**
     * Shows space permessions
     */
    public function actionPermissions()
    {
        $space = $this->getSpace();

        $groups = $space->getUserGroups();
        $groupId = Yii::$app->request->get('groupId', Space::USERGROUP_MEMBER);
        if (!array_key_exists($groupId, $groups)) {
            throw new HttpException(500, 'Invalid group id given!');
        }

        // Handle permission state change
        if (Yii::$app->request->post('dropDownColumnSubmit')) {
            Yii::$app->response->format = 'json';
            $permission = $space->permissionManager->getById(Yii::$app->request->post('permissionId'), Yii::$app->request->post('moduleId'));
            if ($permission === null) {
                throw new \yii\web\HttpException(500, 'Could not find permission!');
            }
            $space->permissionManager->setGroupState($groupId, $permission, Yii::$app->request->post('state'));
            return [];
        }

        return $this->render('permissions', array(
                    'space' => $space,
                    'groups' => $groups,
                    'groupId' => $groupId
        ));
    }

}

?>
