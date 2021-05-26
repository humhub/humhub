<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use Yii;
use humhub\modules\space\modules\manage\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\user\helpers\AuthHelper;
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
        $space->scenario = Space::SCENARIO_SECURITY_SETTINGS;

        if ($space->load(Yii::$app->request->post()) && $space->save()) {
            $this->view->saved();
            return $this->redirect($space->createUrl('index'));
        } else if(Yii::$app->request->post()) {
            $this->view->error(Yii::t('SpaceModule.base', 'Settings could not be saved!'));
        }

        $visibilities = [];
        if ($space->visibility === Space::VISIBILITY_NONE ||
            Yii::$app->user->permissionManager->can(new CreatePrivateSpace)) {
            $visibilities[Space::VISIBILITY_NONE] = Yii::t('SpaceModule.base', 'Private (Invisible)');
        }
        $canCreatePublicSpace = Yii::$app->user->permissionManager->can(new CreatePublicSpace);
        if ($space->visibility === Space::VISIBILITY_REGISTERED_ONLY ||
            $canCreatePublicSpace) {
            $visibilities[Space::VISIBILITY_REGISTERED_ONLY] = Yii::t('SpaceModule.base', 'Public (Registered users only)');
        }
        if ($space->visibility === Space::VISIBILITY_ALL ||
            ($canCreatePublicSpace && AuthHelper::isGuestAccessEnabled())) {
            $visibilities[Space::VISIBILITY_ALL] = Yii::t('SpaceModule.base', 'Visible for all (members and guests)');
        }

        return $this->render('index', ['model' => $space, 'visibilities' => $visibilities]);
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
                throw new HttpException(500, 'Could not find permission!');
            }
            $space->permissionManager->setGroupState($groupId, $permission, Yii::$app->request->post('state'));
            return [];
        }

        return $this->render('permissions', [
                    'space' => $space,
                    'groups' => $groups,
                    'groupId' => $groupId
        ]);
    }

}
