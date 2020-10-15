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
use Laminas\Ldap\Exception\LdapException;
use Laminas\Ldap\Ldap;


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
     *
     * @return string
     */
    public function actionIndex()
    {
        $settings = new LdapSettings();
        $settings->loadSaved();
        if ($settings->load(Yii::$app->request->post()) && $settings->validate() && $settings->save()) {
            $this->view->saved();
            return $this->redirect(['/ldap/admin']);
        }

        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if ($settings->enabled) {
            $enabled = true;
            try {
                /** @var \humhub\modules\ldap\authclient\LdapAuth $ldapAuthClient */
                $ldapAuthClient = Yii::createObject($settings->getLdapAuthDefinition());
                $ldap = $ldapAuthClient->getLdap();
                $userCount = $ldap->count($settings->userFilter, $settings->baseDn, Ldap::SEARCH_SCOPE_SUB);
            } catch (LdapException $ex) {
                $errorMessage = $ex->getMessage();
            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('index', [
            'model' => $settings,
            'enabled' => $enabled,
            'userCount' => $userCount,
            'errorMessage' => $errorMessage
        ]);
    }

}
