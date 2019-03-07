<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\controllers;


use Exception;
use humhub\components\SettingsManager;
use humhub\modules\admin\components\Controller;
use humhub\modules\ldap\models\LdapSettings;
use humhub\modules\user\authclient\LdapAuth;
use Yii;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;


/**
 * Class AdminController
 * @package humhub\modules\ldap\controllers
 */
class AdminController extends Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        parent::init();
    }


    /**
     * Configure Ldap authentication
     * @return string
     */
    public function actionIndex()
    {
        /** @var SettingsManager $settings */
        $settings = Yii::$app->getModule('user')->settings;

        $form = new LdapSettings();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect(['/ldap/admin']);
        }

        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if ($settings->get('auth.ldap.enabled')) {
            $enabled = true;
            try {
                $ldapAuthClient = new LdapAuth();
                $ldap = $ldapAuthClient->getLdap();
                $userCount = $ldap->count(
                    $settings->get('auth.ldap.userFilter'),
                    $settings->get('auth.ldap.baseDn'),
                    Ldap::SEARCH_SCOPE_SUB
                );
            } catch (LdapException $ex) {
                $errorMessage = $ex->getMessage();
            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('index', [
            'model' => $form,
            'enabled' => $enabled,
            'userCount' => $userCount,
            'errorMessage' => $errorMessage
        ]);
    }

}
