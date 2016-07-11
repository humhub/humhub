<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\components\behaviors\AccessControl;

/**
 * ApprovalController handels new user approvals
 */
class AuthenticationController extends Controller
{

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
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
            ]
        ];
    }

    /**
     * Returns a List of Users
     */
    public function actionIndex()
    {
        $form = new \humhub\modules\admin\models\forms\AuthenticationSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
        }

        // Build Group Dropdown
        $groups = [];
        $groups[''] = Yii::t('AdminModule.controllers_SettingController', 'None - shows dropdown in user registration.');
        foreach (\humhub\modules\user\models\Group::find()->all() as $group) {
            if (!$group->is_admin_group) {
                $groups[$group->id] = $group->name;
            }
        }

        return $this->render('authentication', array('model' => $form, 'groups' => $groups));
    }

    public function actionAuthenticationLdap()
    {
        $form = new \humhub\modules\admin\models\forms\AuthenticationLdapSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect(['/admin/authentication/authentication-ldap']);
        }

        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if (Yii::$app->getModule('user')->settings->get('auth.ldap.enabled')) {
            $enabled = true;
            try {
                $ldapAuthClient = new \humhub\modules\user\authclient\ZendLdapClient();
                $ldap = $ldapAuthClient->getLdap();
                $userCount = $ldap->count(
                        Yii::$app->getModule('user')->settings->get('auth.ldap.userFilter'), Yii::$app->getModule('user')->settings->get('auth.ldap.baseDn'), \Zend\Ldap\Ldap::SEARCH_SCOPE_SUB
                );
            } catch (\Zend\Ldap\Exception\LdapException $ex) {
                $errorMessage = $ex->getMessage();
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('authentication_ldap', array('model' => $form, 'enabled' => $enabled, 'userCount' => $userCount, 'errorMessage' => $errorMessage));
    }
}

?>
