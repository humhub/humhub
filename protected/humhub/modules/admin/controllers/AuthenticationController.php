<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Exception;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\AuthenticationLdapSettingsForm;
use humhub\modules\admin\models\forms\AuthenticationSettingsForm;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\user\authclient\ZendLdapClient;
use humhub\modules\user\libs\LdapHelper;
use humhub\modules\user\models\Group;
use Yii;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;

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

    /**
     * Configure Ldap authentication
     * @return string
     */
    public function actionAuthenticationLdap()
    {
        $form = new AuthenticationLdapSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect(['/admin/authentication/authentication-ldap']);
        }

        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if (Yii::$app->getModule('user')->settings->get('auth.ldap.enabled')) {
            $enabled = true;
            try {
                $ldapAuthClient = new ZendLdapClient();
                $ldap = $ldapAuthClient->getLdap();
                $userCount = $ldap->count(
                    Yii::$app->getModule('user')->settings->get('auth.ldap.userFilter'),
					Yii::$app->getModule('user')->settings->get('auth.ldap.baseDn'),
					Ldap::SEARCH_SCOPE_SUB
                );
            } catch (LdapException $ex) {
                $errorMessage = $ex->getMessage();
            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('authentication_ldap', [
			'model' => $form,
			'enabled' => $enabled,
			'userCount' => $userCount,
			'errorMessage' => $errorMessage
		]);
    }

}
