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
class AccountSettingsMenu extends \humhub\widgets\BaseMenu
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
            'label' => Yii::t('UserModule.base', 'Basic Settings'),
            'url' => Url::toRoute(['/user/account/edit-settings']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'user' && Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'edit-settings'),
        ));

        if (count($this->getSecondaryAuthProviders()) != 0) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Connected Accounts'),
                'url' => Url::toRoute(['/user/account/connected-accounts']),
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'user' && Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'connected-accounts'),
            ));
        }

        parent::init();
    }

    /**
     * Returns optional authclients
     * 
     * @return \yii\authclient\ClientInterface[]
     */
    protected function getSecondaryAuthProviders()
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
