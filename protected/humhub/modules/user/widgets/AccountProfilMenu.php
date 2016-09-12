<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use yii\helpers\Url;

/**
 * Account Settings Tab Menu
 */
class AccountProfilMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/tabMenu";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addItem(array(
            'label' => Yii::t('UserModule.base', 'General'),
            'url' => Url::toRoute(['/user/account/edit']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'user' && Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'edit'),
        ));

        if (Yii::$app->user->canChangeEmail()) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Change Email'),
                'url' => Url::toRoute(['/user/account/change-email']),
                'sortOrder' => 200,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'user' && Yii::$app->controller->id == 'account' && (Yii::$app->controller->action->id == 'change-email' || Yii::$app->controller->action->id == 'change-email-validate')),
            ));
        }

        if (Yii::$app->user->canChangePassword()) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Change Password'),
                'url' => Url::toRoute(['/user/account/change-password']),
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'user' && Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'change-password'),
            ));
        }

        if (Yii::$app->user->canDeleteAccount()) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Delete Account'),
                'url' => Url::toRoute(['/user/account/delete']),
                'sortOrder' => 400,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'user' && Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'delete'),
            ));
        }

        parent::init();
    }

    /**
     * Returns optional authclients
     * 
     * @return \yii\authclient\ClientInterface[]
     */
    protected function getSecondoaryAuthProviders()
    {
        $clients = [];
        foreach (Yii::$app->get('authClientCollection')->getClients() as $client) {
            if (!$client instanceof \humhub\modules\user\authclient\BaseFormAuth && !$client instanceof \humhub\modules\user\authclient\interfaces\PrimaryClient) {
                $clients[] = $client;
            }
        }

        return $clients;
    }

}
