<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\AuthenticationSettingsForm;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\user\models\Group;
use Yii;

/**
 * ApprovalController handels new user approvals
 */
class AuthenticationController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'basic' => Yii::t('AdminModule.base', 'Basic'),
            'authentication' => Yii::t('AdminModule.base', 'Authentication'),
            'authentication-ldap' => Yii::t('AdminModule.base', 'Authentication')
        ]);

        $this->subLayout = '@admin/views/layouts/user';

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => ManageSettings::class]
        ];
    }

    /**
     * Returns a List of Users
     * @return string
     */
    public function actionIndex()
    {
        $form = new AuthenticationSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
        }

        // Build Group Dropdown
        $groups = [
            '' => Yii::t(
                'AdminModule.controllers_SettingController',
                'None - shows dropdown in user registration.'
            )
        ];

        foreach (Group::find()->all() as $group) {
            if (!$group->is_admin_group) {
                $groups[$group->id] = $group->name;
            }
        }

        return $this->render('authentication', [
            'model' => $form,
            'groups' => $groups
        ]);
    }
}
