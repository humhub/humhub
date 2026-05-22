<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\controllers;

use Exception;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\ldap\models\LdapSettings;
use humhub\modules\ldap\Module;
use Yii;

/**
 * Class AdminController
 * @package humhub\modules\ldap\controllers
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => [ManageSettings::class]],
        ];
    }

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

        // Registry is populated from the DB-backed LdapSettings. If the model
        // is "enabled" (checkbox checked, possibly only in POST after a failed
        // save) but DB isn't yet, the 'ldap' connection isn't registered —
        // skip the status box silently so the user just sees the form errors.
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $registry = $module->getConnectionRegistry();
        if ($settings->enabled && $registry->has('ldap')) {
            $enabled = true;
            try {
                $userCount = $registry->getService('ldap')->countUsers();
            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('index', [
            'model' => $settings,
            'enabled' => $enabled,
            'userCount' => $userCount,
            'errorMessage' => $errorMessage,
            'authClientOptions' => $settings->getAuthClientOptions(),
        ]);
    }
}
